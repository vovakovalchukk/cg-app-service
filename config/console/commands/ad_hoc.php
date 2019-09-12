<?php
use CG\Cache\InvalidationHandler;
use CG\CGLib\Command\EnsureProductsAndListingsAssociatedWithRootOu;
use CG\Db\Mysqli;
use CG\Gearman\Client as GearmanClient;
use CG\Order\Client\Gearman\Generator\UpdateExchangeRate;
use CG\Order\Client\Gearman\WorkerFunction\SetInvoiceByOU as WorkerFunction;
use CG\Order\Client\Gearman\WorkerFunction\SetItemImages as SetItemImagesWorkerFunction;
use CG\Order\Client\Gearman\Workload\SetInvoiceByOU as Workload;
use CG\Order\Client\Gearman\Workload\SetItemImages as SetItemImagesWorkload;
use CG\Order\Client\StorageInterface as OrderStorage;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Shared\Collection as Orders;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Entity as OrderItem;
use CG\Order\Shared\Item\Filter as ItemFilter;
use CG\Order\Shared\Item\StorageInterface as OrderItemStorage;
use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
use CG\Settings\Invoice\Shared\Filter as InvoiceSettingsFilter;
use CG\Settings\Invoice\Shared\Repository as InvoiceSettingsRepository;
use CG\Stock\Adjustment as StockAdjustment;
use CG\Stock\Command\Adjustment as StockAdjustmentCommand;
use CG\Stock\Command\FindIncorrectlyAllocatedStock as FindIncorrectlyAllocatedStockCommand;
use CG\Template\Mapper as TemplateMapper;
use CG\Template\Service as TemplateService;
use Predis\Client as Redis;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Di\Di;

/** @var Di $di */
return [
    'ad-hoc:retrofitInvoiceNumbers' => [
        'description' => 'Retro fit sequential invoice Numbers for existing orders in CG',
        'arguments' => [],
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            /**
             * @var GearmanClient $gearmanClient
             */
            $gearmanClient = $di->get(GearmanClient::class);

            $sqlClient = $di->get('ReadCGSql');
            $query = 'SELECT organisationUnitId, COUNT(*) AS orderCount
                FROM `order`
                GROUP BY organisationUnitId
                ORDER BY orderCount DESC';
            $results = $sqlClient->getAdapter()->query($query)->execute();
            foreach ($results as $row) {
                $ouId = $row['organisationUnitId'];
                $workload = new Workload($ouId);
                $gearmanClient->doBackground(
                    WorkerFunction::FUNCTION_NAME,
                    serialize($workload),
                    WorkerFunction::FUNCTION_NAME . '-' . $ouId
                );
            }
        },
    ],
    'ad-hoc:correctAllocatedStock' => [
        'description' => 'Correct any discrepancies with allocated stock, by default will display proposed changes and not apply',
        'arguments' => [
            'organisationUnitId' => [
                'required' => false
            ],
            'sku' => [
                'required' => false
            ],
        ],
        'options' => [
            'fix' => [
                'description' => 'Apply updates to stock',
            ],
        ],
        'command' =>
            function(InputInterface $input, OutputInterface $output) use ($di)
            {
                $findCommand = $di->get(FindIncorrectlyAllocatedStockCommand::class);
                $adjustments = $findCommand->findIncorrectlyAllocated(
                    null,
                    $input->getArgument('organisationUnitId'),
                    $input->getArgument('sku')
                );

                /** @var StockAdjustmentCommand $command */
                $command = $di->get(StockAdjustmentCommand::class);
                $command(
                    $input,
                    $output,
                    [],
                    iterator_to_array($adjustments),
                    $input->getOption('fix'),
                    ['Unknown Orders']
                );
            },
        'modulus' => true,
    ],
    'ad-hoc:correctAllocatedStockForOU' => [
        'description' => 'Correct any discrepancies with allocated stock, by default will display proposed changes and not apply',
        'arguments' => [
            'organisationUnitId' => [
                'required' => true
            ],
        ],
        'options' => [
            'fix' => [
                'description' => 'Apply updates to stock',
            ],
        ],
        'command' =>
            function(InputInterface $input, OutputInterface $output) use ($di)
            {
                $findCommand = $di->get(FindIncorrectlyAllocatedStockCommand::class);
                $adjustments = $findCommand->findUnderAllocated($input->getArgument('organisationUnitId'));

                /** @var StockAdjustmentCommand $command */
                $command = $di->get(StockAdjustmentCommand::class);
                $command(
                    $input,
                    $output,
                    StockAdjustment::TYPE_ALLOCATED,
                    iterator_to_array($adjustments),
                    $input->getOption('fix')
                );
            },
    ],
    'ad-hoc:correctOverAllocatedStockForOU' => [
        'description' => 'Correct any discrepancies with allocated stock where we have over allocated, by default will display proposed changes and not apply',
        'arguments' => [
            'organisationUnitId' => [
                'required' => true
            ],
        ],
        'options' => [
            'fix' => [
                'description' => 'Apply updates to stock',
            ],
        ],
        'command' =>
            function(InputInterface $input, OutputInterface $output) use ($di)
            {
                $findCommand = $di->get(FindIncorrectlyAllocatedStockCommand::class);
                $adjustments = $findCommand->findOverAllocated($input->getArgument('organisationUnitId'));

                /** @var StockAdjustmentCommand $command */
                $command = $di->get(StockAdjustmentCommand::class);
                $command(
                    $input,
                    $output,
                    [StockAdjustment::TYPE_ALLOCATED, StockAdjustment::TYPE_ONHAND],
                    iterator_to_array($adjustments),
                    $input->getOption('fix'),
                    ['Unknown Orders']
                );
            },
    ],
    'ad-hoc:validateOrderItemStatus' => [
        'description' => 'Reports any new order items that do not match their orders status since the command last run',
        'arguments' => [],
        'options' => [
            'all' => [
                'description' => 'Get all orders, even if we have previously reported on it',
            ],
        ],
        'command' =>
            function(InputInterface $input, OutputInterface $output) use ($di) {
                $query = <<<EOF
SELECT o.`id` as `orderId`, i.`id` as `orderItemId`, o.`channel`, o.`status` as `orderStatus`, i.`status` as `itemStatus`
FROM `order` o
JOIN item i ON o.id = i.`orderId`
WHERE o.`status` != i.`status`
ORDER BY o.`id`, i.`id`
EOF;

                /** @var Redis $redis */
                $redis = $di->get('reliable_redis');
                /** @var Adapter $cgApp */
                $cgApp = $di->get('cg_appReadSql')->getAdapter();
                /** @var ResultInterface $orderItems */
                $orderItems = $cgApp->query($query)->execute();

                $now = time();
                $lastRun = (int) $redis->getset('ValidateOrderItemStatus:LastRun', (string) $now);
                $fetchAll = $input->getOption('all');

                $count = 0;
                $table = (new Table($output))
                    ->setHeaders(['OrderId', 'OrderItemId', 'Channel', 'OrderStatus', 'OrderItemStatus']);

                foreach ($orderItems as $orderItem) {
                    if (!$redis->sadd('ValidateOrderItemStatus:OrderItemId', $orderItem['orderItemId']) && !$fetchAll ) {
                        continue;
                    }

                    $count++;
                    $table->addRow($orderItem);
                }

                if ($count == 0) {
                    return;
                }

                $output->writeln('The following order items have a different status to their order:');
                $table->render();

                if ($lastRun && !$fetchAll) {
                    $output->writeln(date('d/m/Y H:i:s', $lastRun) . ' - ' . date('d/m/Y H:i:s', $now));
                }
            }
    ],
    'ad-hoc:updateOrderItemStatus' => [
        'description' => 'Update all order items where their status does not match their order status, by default will just output current status',
        'arguments' => [],
        'options' => [
            'fix' => [
                'description' => 'Update item statuses',
            ],
        ],
        'command' =>
            function(InputInterface $input, OutputInterface $output) use ($di) {
                $output->getFormatter()->setStyle('b', new OutputFormatterStyle(null, null, ['bold']));
                $output->getFormatter()->setStyle('empty', new OutputFormatterStyle('red', null, ['bold']));

                $query = <<<EOF
SELECT DISTINCT  o.`id` as `orderId`
FROM `order` o
JOIN item i ON o.id = i.`orderId`
WHERE o.`status` != i.`status`
AND (o.lastUpdateFromChannel <= DATE_SUB(NOW(), INTERVAL 6 HOUR) OR o.lastUpdateFromChannel IS NULL)
ORDER BY o.`purchaseDate`
EOF;

                /** @var Mysqli $cgApp */
                $cgApp = $di->get('cg_appReadMysqli');
                /** @var OrderStorage $orderStorage */
                $orderStorage = $di->get($di->instanceManager()->getTypePreferences(OrderStorage::class)[0]);
                /** @var OrderItemStorage $orderItemStorage */
                $orderItemStorage = $di->get($di->instanceManager()->getTypePreferences(OrderItemStorage::class)[0]);

                if (!$input->getOption('fix')) {
                    $output->writeln('<b>Performing dry run, no order items will be updated. Please specify --fix to apply changes.</b>');
                    $output->writeln('');
                }

                $orderIds = $cgApp->fetchColumn('orderId', $query);
                if (empty($orderIds)) {
                    $output->writeln('<empty>No Orders found with items in a different status</empty>');
                    return;
                }

                $format = ' %current%/%max% [%bar%] %percent:3s%%';
                $overwrite = true;
                if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                    $format = ' %message%' . "\n" . $format;
                    $overwrite = false;
                }

                $progress = new ProgressBar($output, count($orderIds));
                $progress->setMessage('');
                $progress->setFormat($format);
                $progress->setOverwrite($overwrite);
                $progress->start();


                foreach (array_chunk($orderIds, 100) as $batchOrderIds) {
                    $filter = (new OrderFilter('all', 1))->setOrderIds($batchOrderIds);
                    /** @var Orders $orders */
                    $orders = $orderStorage->fetchCollectionByFilter($filter);

                    /** @var Order $order */
                    foreach ($orders as $order) {
                        /** @var OrderItem $orderItem */
                        foreach ($order->getItems() as $orderItem) {
                            if ($order->getStatus() == $orderItem->getStatus()) {
                                continue;
                            }

                            $progress->setMessage(sprintf('Updating %s [%s => %s]', $orderItem->getId(), $orderItem->getStatus(), $order->getStatus()));
                            if ($input->getOption('fix')) {
                                $orderItemStorage->save(
                                    $orderItem->setStatus($order->getStatus())
                                );
                            }
                        }
                        $progress->advance();
                    }
                }

                $output->writeln('');
                $output->writeln('');
            }
    ],
    'ad-hoc:updateOrderItemImages' => [
        'description' => 'Update all order items images by retrieving the correct image id from its listing/product/unimported listing',
        'arguments' => [],
        'command' =>
            function(InputInterface $input, OutputInterface $output) use ($di) {
                $gearmanClient = $di->get(GearmanClient::class);

                $query = "SELECT DISTINCT(i.id) FROM `item` i
                          INNER JOIN `itemImage` ii ON ii.itemId = i.id
                          INNER JOIN `order` o ON o.id = i.orderId";

                /** @var Mysqli $cgApp */
                $cgApp = $di->get('cg_appReadMysqli');
                /** @var OrderItemStorage $orderItemStorage */
                $orderItemStorage = $di->get($di->instanceManager()->getTypePreferences(OrderItemStorage::class)[0]);

                $itemIds = $cgApp->fetchColumn('id', $query);

                $format = ' %current%/%max% [%bar%] %percent:3s%%';
                $overwrite = true;
                if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                    $format = ' %message%' . "\n" . $format;
                    $overwrite = false;
                }

                $progress = new ProgressBar($output, count($itemIds));
                $progress->setMessage('');
                $progress->setFormat($format);
                $progress->setOverwrite($overwrite);
                $progress->start();


                foreach (array_chunk($itemIds, 100) as $batchItemIds) {
                    $filter = (new ItemFilter('all', 1))->setId($batchItemIds);
                    /** @var Orders $orders */
                    $items = $orderItemStorage->fetchCollectionByFilter($filter);

                    /** @var Order $order */
                    foreach ($items as $item) {
                        $workload = new SetItemImagesWorkload($item);
                        $gearmanClient->doLowBackground(
                            SetItemImagesWorkerFunction::FUNCTION_NAME,
                            serialize($workload),
                            SetItemImagesWorkerFunction::FUNCTION_NAME . '-' . $item->getId()
                        );
                        $progress->advance();
                    }
                }

                $output->writeln('');
                $output->writeln('');
            }
    ],
    'ad-hoc:ensureProductsAndListingsAssociatedWithRootOu' => [
        'description' => 'Find any Products, Listings or UnimportedListings associated with a Trading Company and correct them to point at their root OrganisationUnit instead.',
        'arguments' => [],
        'options' => [],
        'command' => function(InputInterface $input, OutputInterface $output) use ($di)
            {
                $output->writeln('Starting ensureProductsAndListingsAssociatedWithRootOu command');
                /** @var EnsureProductsAndListingsAssociatedWithRootOu $command */
                $command = $di->get(EnsureProductsAndListingsAssociatedWithRootOu::class);
                $ouCount = $command();
                $output->writeln('Finished, ' . $ouCount . ' OUs corrected. See logs for details.');
            }
    ],

    'ad-hoc:preserveStyleLInvoiceTemplate' => [
        'description' => 'For any OUs that have their invoice mapping explicitly set as the now deprecated Stlye L template copy it over to their custom templates so it is not lost',
        'arguments' => [],
        'options' => [],
        'command' => function(InputInterface $input, OutputInterface $output) use ($di)
            {
                $output->writeln('Starting preserveStyleLInvoiceTemplate command');

                $mongo = $di->get('mongodb');
                $mongoCollection = $mongo->settings->invoice;
                $results = $mongoCollection->find(["default" => new \MongoRegex('/^default-styleL_OU.+/')]);
                if (!$results->count(true)) {
                    $output->writeln('No results found');
                    return;
                }
                $ouIds = [];
                foreach ($results as $result) {
                    $ouIds[] = $result['_id'];
                }
                $settingsService = $di->get(InvoiceSettingsService::class);
                $templateService = $di->get(TemplateService::class);
                $templateMapper = $di->get(TemplateMapper::class);
                $template = $templateService->fetch('default-styleL_OU0');
                $template->setId(null)
                    ->setStoredETag(null)
                    ->setName('DUPLICATE - Default Style L');
                foreach ($ouIds as $ouId) {
                    $ouTemplate = clone $template;
                    $ouTemplate->setOrganisationUnitId($ouId);
                    $savedOuTemplate = $templateMapper->fromHal($templateService->save($ouTemplate));
                    $output->writeln('  Duplicated template for OU ' . $ouId . ', ID: ' . $savedOuTemplate->getId());
                    $settings = $settingsService->fetch($ouId);
                    $settings->setDefault($savedOuTemplate->getId());
                    $settingsService->save($settings);
                }

                $output->writeln('Finished, ' . count($ouIds) . ' OUs have had templates created.');
            }
    ],

    'ad-hoc:populateCacheInvalidationMapsOfMaps' => [
        'description' => 'Create maps of all the invalidation maps',
        'arguments' => [],
        'options' => [],
        'command' => function(InputInterface $input, OutputInterface $output) use ($di)
        {
            $output->writeln('Starting populateCacheInvalidationMapsOfMaps command');

            $redis = $di->get('reliable_redis');
            $mapTypes = [
                InvalidationHandler::COLLECTION_DEPENDENCY_MAP_PREFIX,
                InvalidationHandler::COLLECTION_TYPE_MAP_PREFIX,
                InvalidationHandler::COLLECTION_ENTITY_MAP_PREFIX,
                InvalidationHandler::ENTITY_RELATED_MAP_PREFIX,
                InvalidationHandler::ENTITY_FIELD_MAP_PREFIX,
            ];

            foreach ($mapTypes as $mapType) {
                $output->writeln('Scanning for ' . $mapType);
                $mapsByEntityType = [];

                $cursor = 0;
                do {
                    list($cursor, $results) = $redis->scan($cursor, 'MATCH', $mapType.':*');
                    $output->writeln('  Got ' . count($results) . ' results, cursor now: ' . $cursor);

                    foreach ($results as $result) {
                        list(, $entityType) = explode(':', $result);
                        if (!isset($mapsByEntityType[$entityType])) {
                            $mapsByEntityType[$entityType] = [];
                        }
                        $mapsByEntityType[$entityType][] = base64_encode($result);
                    }
                } while ($cursor != 0);

                $output->writeln('  Finished scanning. Adding maps to map-of-maps.');
                foreach ($mapsByEntityType as $entityType => $maps) {
                    $mapOfMapsKey = $mapType . 'Map:' . $entityType;
                    // We can call SADD with multiple members but its unwise to do too many at once
                    $chunkedMaps = array_chunk($maps, 50);
                    foreach ($chunkedMaps as $chunk) {
                        // In PHP 5.6+ we could use the splat ('...') operator here
                        call_user_func_array([$redis, 'sadd'], array_merge([$mapOfMapsKey], $chunk));
                    }
                }
            }
        }
    ],

    'ad-hoc:populateInvoiceSettingEmailSendAs' => [
        'description' => 'Populate the Settings\\Invoice::emailSendAs field for existing users with autoEmail turned on',
        'arguments' => [],
        'options' => [],
        'command' => function(InputInterface $input, OutputInterface $output) use ($di)
        {
            $settingsStorage = $di->get(InvoiceSettingsRepository::class);
            $filter = (new InvoiceSettingsFilter())
                ->setLimit('all')
                ->setPage(1);
            $settings = $settingsStorage->fetchCollectionByFilter($filter);
            $output->writeln("Got ".$settings->count()." settings");

            foreach ($settings as $ouSettings) {
                $output->writeln("Processing for OU " . $ouSettings->getId() . ". AutoEmail?: " . ($ouSettings->getAutoEmail() ? 'yes' : 'no') . ', EmailSendAs?: ' . ($ouSettings->getEmailSendAs() ? 'yes':'no'));
                if (!$ouSettings->getAutoEmail() || $ouSettings->getEmailSendAs()) {
                    continue;
                }
                $ouSettings->setEmailSendAs('no-reply@orderhub.io')
                    ->setAutoEmailAllowed(true)
                    ->setEmailVerified(true);
                $settingsStorage->save($ouSettings);
            }
        }
    ],

    'ad-hoc:updateInvoiceSettingTradingCompaniesStructure' => [
        'description' => 'Update the Settings\\Invoice::tradingCompanies structure to match the new format',
        'arguments' => [],
        'options' => [],
        'command' => function(InputInterface $input, OutputInterface $output) use ($di)
        {
            $settingsStorage = $di->get(InvoiceSettingsRepository::class);
            $filter = (new InvoiceSettingsFilter())
                ->setLimit('all')
                ->setPage(1);
            $settings = $settingsStorage->fetchCollectionByFilter($filter);
            $output->writeln("Got ".$settings->count()." settings");

            foreach ($settings as $ouSettings) {
                $output->writeln("Processing for OU " . $ouSettings->getId() . ". Trading Companies?: " . (!empty($ouSettings->getTradingCompanies()) ? 'yes' : 'no'));
                if (empty($ouSettings->getTradingCompanies())) {
                    continue;
                }
                $formattedTradingCompanies = [];
                foreach ($ouSettings->getTradingCompanies() as $key => $value) {
                    if (is_array($value)) {
                        continue;
                    }
                    $formattedTradingCompanies[$key] = [
                        'id' => $key,
                        'assignedInvoice' => $value,
                        'emailSendAs' => null,
                        'emailVerified' => false,
                        'emailVerificationStatus' => false,
                    ];
                }
                if (empty($formattedTradingCompanies)) {
                    continue;
                }

                $ouSettings->setTradingCompanies($formattedTradingCompanies);
                $settingsStorage->save($ouSettings);
            }
        }
    ],
    'ad-hoc:updateOrderExchangeRates' => [
        'command' => function(InputInterface $input, OutputInterface $output) use ($di) {
            /** @var UpdateExchangeRate $updateExchangeRate */
            $updateExchangeRate = $di->get(UpdateExchangeRate::class);
            /** @var Mysqli $cgApp */
            $cgApp = $di->get('cg_appReadMysqli');

            $page = 0;
            $count = 0;
            $select = <<<SQL
SELECT DISTINCT o.`id`
FROM `order` o
WHERE `exchangeRate` IS NULL
AND EXISTS (SELECT `organisationUnitId` FROM billing.subscription WHERE o.`rootOrganisationUnitId` = `organisationUnitId` AND NOW() BETWEEN `fromDate` AND IFNULL(`toDate`, NOW()))
ORDER BY o.`purchaseDate` DESC
SQL;

            $output->writeln('Generating jobs...');
            while (!empty($orderIds = $cgApp->fetchColumn('id', $select . ' LIMIT ' . (1000 * $page++) . ',1000'))) {
                foreach ($orderIds as $orderId) {
                    if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                        $output->writeln(sprintf('Generating job for order %s', $orderId));
                    }
                    ($updateExchangeRate)($orderId);
                    $count++;
                }
            }
            $output->writeln(sprintf('Generated %d jobs', $count));
        },
        'description' => 'Triggers a job to update exchangerates for any orders that don\'t have one',
    ],
    'ad-hoc:setAmazonCategoriesVersion' => [
        'command' => function(InputInterface $input, OutputInterface $output) use ($di) {
            /** @var Mysqli $cgApp */
            $cgApp = $di->get('cg_appReadMysqli');
            /* @var $command \CG\Amazon\Command\UpdateCategory */
            $command = $di->get(CG\Amazon\Command\UpdateCategory::class);

            $page = 0;
            $count = 0;
            $select = <<<SQL
SELECT MAX(`id`) AS `id`
FROM `category`
WHERE `channel`='amazon' AND `parentId` = 0 GROUP BY `title`, `marketplace` ORDER BY `title`
SQL;

            $marketplaces = [];
            $output->writeln('Fetching parent categories ...');
            while (!empty($categoryIds = $cgApp->fetchColumn('id', $select . ' LIMIT ' . (1000 * $page++) . ',1000'))) {
                foreach ($categoryIds as $categoryId) {
                    if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                        $output->writeln(sprintf('Updating parent category %s', $categoryId));
                    }
                    $mpl = $command->addCategoryVersion($categoryId, $output);
                    $marketplaces = array_merge($marketplaces, $mpl);
                    $count++;
                }
            }

            $command->addCategoryVersionMap($marketplaces);
            $output->writeln(sprintf('Updated %d parent categories', $count));
        },
        'description' => 'Setting version to all latest Amazon categories',
    ]
];
