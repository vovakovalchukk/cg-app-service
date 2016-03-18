<?php
use CG\CGLib\Command\EnsureProductsAndListingsAssociatedWithRootOu;
use CG\Db\Mysqli;
use CG\Gearman\Client as GearmanClient;
use CG\Order\Client\Gearman\WorkerFunction\SetInvoiceByOU as WorkerFunction;
use CG\Order\Client\Gearman\Workload\SetInvoiceByOU as Workload;
use CG\Order\Shared\Item\StorageInterface as OrderItemStorage;
use CG\Order\Client\StorageInterface as OrderStorage;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Shared\Collection as Orders;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Entity as OrderItem;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Adjustment as StockAdjustment;
use CG\Stock\Command\Adjustment as StockAdjustmentCommand;
use Predis\Client as Redis;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Di\Di;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

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
        'arguments' => [],
        'options' => [
            'fix' => [
                'description' => 'Apply updates to stock',
            ],
        ],
        'command' =>
            function(InputInterface $input, OutputInterface $output) use ($di) {
                $query = <<<EOF
SELECT s.sku, calc.organisationUnitId, calculatedAllocated as expected, sl.allocated as actual, calculatedAllocated - allocated as diff
FROM stock AS s
INNER JOIN stockLocation AS sl ON s.id = sl.stockId
INNER JOIN (
	SELECT itemSku, item.organisationUnitId, SUM(
		IF(item.purchaseDate > account.cgCreationDate,
			IF(`status` IN ('awaiting payment', 'new','cancelling','dispatching','refunding'), itemQuantity, 0),
			IF(`status` IN ('awaiting payment', 'new'), itemQuantity, 0)
		)) as calculatedAllocated
	FROM item
	INNER JOIN account.account ON item.accountId = account.id
	WHERE item.stockManaged = 1
	GROUP BY itemSku, item.organisationUnitId
) as calc ON calc.itemSku LIKE s.sku AND s.organisationUnitId = calc.organisationUnitId
WHERE calculatedAllocated > allocated
ORDER BY organisationUnitId, sku
EOF;

                /** @var StockAdjustmentCommand $command */
                $command = $di->get(StockAdjustmentCommand::class);
                /** @var Adapter $adapter */
                $adapter = $di->get('cg_appReadSql')->getAdapter();
                /** @var ResultInterface $adjustments */
                $adjustments = $adapter->query($query)->execute();

                $command(
                    $input,
                    $output,
                    StockAdjustment::TYPE_ALLOCATED,
                    iterator_to_array($adjustments),
                    $input->getOption('fix')
                );
            }
    ],
    'ad-hoc:correctOverAllocatedStock' => [
        'description' => 'Correct any discrepancies with allocated stock where we have over allocated, by default will display proposed changes and not apply',
        'arguments' => [],
        'options' => [
            'fix' => [
                'description' => 'Apply updates to stock',
            ],
        ],
        'command' =>
            function(InputInterface $input, OutputInterface $output) use ($di) {
                $query = <<<EOF
SELECT s.sku, calc.organisationUnitId, calculatedAllocated as expected, sl.allocated as actual, calculatedAllocated - allocated as diff, unknownOrders
FROM stock AS s
INNER JOIN stockLocation AS sl ON s.id = sl.stockId
INNER JOIN (
	SELECT itemSku, item.organisationUnitId, SUM(
		IF(item.purchaseDate > account.cgCreationDate,
			IF(`status` IN ('awaiting payment', 'new','cancelling','dispatching','refunding'), itemQuantity, 0),
			IF(`status` IN ('awaiting payment', 'new'), itemQuantity, 0)
		)) as calculatedAllocated,
        SUM(IF(`status` = 'unknown', itemQuantity, 0)) as unknownOrders
	FROM item
	INNER JOIN account.account ON item.accountId = account.id
	WHERE item.stockManaged = 1
	GROUP BY itemSku, item.organisationUnitId
) as calc ON calc.itemSku LIKE s.sku AND s.organisationUnitId = calc.organisationUnitId
WHERE calculatedAllocated < allocated
ORDER BY organisationUnitId, sku
EOF;

                /** @var StockAdjustmentCommand $command */
                $command = $di->get(StockAdjustmentCommand::class);
                /** @var Adapter $adapter */
                $adapter = $di->get('cg_appReadSql')->getAdapter();
                /** @var ResultInterface $adjustments */
                $adjustments = $adapter->query($query)->execute();

                $command(
                    $input,
                    $output,
                    [StockAdjustment::TYPE_ALLOCATED, StockAdjustment::TYPE_ONHAND],
                    iterator_to_array($adjustments),
                    $input->getOption('fix'),
                    ['Unknown Orders']
                );
            }
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

                $filter = (new OrderFilter(500, 1))->setOrderIds($orderIds);
                try {
                    do {
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
                    } while ($filter->setPage($filter->getPage() + 1));
                } catch (NotFound $exception) {
                    // No more orders to update
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
                $command = $di->get(EnsureProductsAndListingsAssociatedWithRootOu::class);
                $ouCount = $command();
                $output->writeln('Finished, ' . $ouCount . ' OUs corrected. See logs for details.');
            }
    ],
];
