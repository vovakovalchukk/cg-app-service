<?php
use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as Accounts;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Filter as AccountFilter;
use CG\Listing\Unimported\Marketplace\Entity as Marketplace;
use CG\Listing\Unimported\Marketplace\Filter as MarketplaceFilter;
use CG\Listing\Unimported\Marketplace\Service as MarketplaceService;
use CG\Settings\Invoice\Service\Storage\MongoDb as InvoiceSettingsMongoDb;
use CG\Settings\InvoiceMapping\Entity as InvoiceMapping;
use CG\Settings\InvoiceMapping\Mapper as InvoiceMappingMapper;
use CG\Settings\InvoiceMapping\Service as InvoiceMappingService;
use CG\Stdlib\Exception\Runtime\NotFound;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Di\Di;
use CG\Template\Command\MigrateMongoDataToMysql as MigrateMongoTemplateDataToMysql;
use CG\UserPreference\Command\MigrateMongoDataToMysql as MigrateMongoUserPreferenceDataToMysql;

/** @var Di $di */
return [
    'phinx:defaultUseVerifiedEmailAddressForAmazonInvoices' => [
        'command' => function(InputInterface $input, OutputInterface $output) use ($di) {
            /** @var \MongoClient $mongoClient */
            $mongoClient = $di->get('mongodb');

            /** @var \MongoCollection $collection */
            $collection = $mongoClient
                ->{InvoiceSettingsMongoDb::MONGO_DATABASE}
                ->{InvoiceSettingsMongoDb::MONGO_COLLECTION};

            $collection->update(
                ['useVerifiedEmailAddressForAmazonInvoices' => ['$exists' => false]],
                ['$set' => ['useVerifiedEmailAddressForAmazonInvoices' => false]],
                ['multiple' => true]
            );
        },
        'description' => 'Set the default value for `useVerifiedEmailAddressForAmazonInvoices` on invoice settings',
    ],
    'phinx:migrateOUInvoiceMappings' => [
        'command' => function(InputInterface $input, OutputInterface $output) use ($di) {
            /** @var \MongoClient $mongoClient */
            $mongoClient = $di->get('mongodb');

            /** @var \MongoCollection $collection */
            $collection = $mongoClient
                ->{InvoiceSettingsMongoDb::MONGO_DATABASE}
                ->{InvoiceSettingsMongoDb::MONGO_COLLECTION};

            $ouMap = [];
            $cursor = $collection->find([], ['_id' => true, 'default' => true, 'tradingCompanies' => true]);

            $output->writeln(sprintf('Found %d invoice settings', $cursor->count()));

            foreach ($cursor as $invoiceSetting) {
                if (isset($invoiceSetting['default']) && $invoiceSetting['default'] != '') {
                    $ouMap[$invoiceSetting['_id']] = [$invoiceSetting['_id'], $invoiceSetting['default']];
                }

                if (
                    !isset($invoiceSetting['tradingCompanies'])
                    || !is_array($invoiceSetting['tradingCompanies'])
                    || empty($invoiceSetting['tradingCompanies'])
                ) {
                    continue;
                }

                foreach ($invoiceSetting['tradingCompanies'] as $tradingCompanies) {
                    if (!isset($tradingCompanies['id'], $tradingCompanies['assignedInvoice'])) {
                        continue;
                    }

                    if ($tradingCompanies['assignedInvoice'] == '') {
                        continue;
                    }

                    $ouMap[$tradingCompanies['id']] = [$invoiceSetting['_id'], $tradingCompanies['assignedInvoice']];
                }
            }

            $output->writeln(sprintf('Found %d ous for found invoice settings', count($ouMap)));

            if (empty($ouMap)) {
                return;
            }

            /** @var AccountService $accountService */
            $accountService = $di->get(AccountService::class);
            try {
                /** @var Accounts $accounts */
                $accounts = $accountService->fetchByFilter(
                    (new AccountFilter('all', 1))->setOrganisationUnitId(array_keys($ouMap))
                );
            } catch (NotFound $exception) {
                return;
            }

            $output->writeln(sprintf('Found %d accounts for the found ous', $accounts->count()));

            /** @var MarketplaceService $marketplaceService */
            $marketplaceService = $di->get(MarketplaceService::class);
            $marketplaceMap = [];
            try {
                $marketplaces = $marketplaceService->fetchCollectionByFilter(
                    (new MarketplaceFilter())->setAccountId($accounts->getIds())
                );

                /** @var Marketplace $marketplace */
                foreach ($marketplaces as $marketplace) {
                    if (!isset($marketplaceMap[$marketplace->getAccountId()])) {
                        $marketplaceMap[$marketplace->getAccountId()] = [];
                    }
                    $marketplaceMap[$marketplace->getAccountId()][] = $marketplace->getMarketplace();
                }
            } catch (NotFound $exception) {
                // No marketplaces - will use default for all accounts
            }

            /** @var InvoiceMappingService $invoiceMappingsService */
            $invoiceMappingsService = $di->get(InvoiceMappingService::class);
            /** @var InvoiceMappingMapper $invoiceMappingsMapper */
            $invoiceMappingsMapper = $di->get(InvoiceMappingMapper::class);

            /** @var Account $account */
            foreach ($accounts as $account) {
                $marketplaces = $marketplaceMap[$account->getId()] ?? [InvoiceMapping::DEFAULT_SITE];
                if (!isset($ouMap[$account->getOrganisationUnitId()])) {
                    continue;
                }
                list($ouId, $invoiceId) = $ouMap[$account->getOrganisationUnitId()];

                $output->writeln(sprintf('Mapping %d sites to invoiceId "%s" for account %d', count($marketplaces), $invoiceId, $account->getId()));

                foreach ($marketplaces as $marketplace) {
                    $invoiceMappingsService->save(
                        $invoiceMappingsMapper->fromArray(
                            [
                                'organisationUnitId' => $ouId,
                                'accountId' => $account->getId(),
                                'site' => $marketplace,
                                'invoiceId' => $invoiceId,
                            ]
                        )
                    );
                }
            }
        },
        'description' => 'Migrate OU invoice mappings from the invoice settings entity to the new invoice mappings entity',
    ],
    'phinx:migrateMongoTemplateDataToMysql' => [
        'command' => function (InputInterface $input, OutputInterface $output) use ($di)
        {
            $output->writeln('Migrating Transaction data from MongoDB to MySQL');
            $command = $di->get(MigrateMongoTemplateDataToMysql::class);
            $count = $command->migrate();
            $output->writeln('Finished migration of Templates, ' . $count . ' processed');
        },
        'description' => 'Copies Transaction data over from MongoDB to MySQL',
        'arguments' => [],
        'options' => []
    ],
    'phinx:migrateMongoUserPreferenceDataToMysql' => [
        'command' => function (InputInterface $input, OutputInterface $output) use ($di)
        {
            $output->writeln('Migrating Transaction data from MongoDB to MySQL');
            $command = $di->get(MigrateMongoUserPreferenceDataToMysql::class);
            $count = $command->migrate();
            $output->writeln('Finished migration of User Preferences, ' . $count . ' processed');
        },
        'description' => 'Copies Transaction data over from MongoDB to MySQL',
        'arguments' => [],
        'options' => []
    ],
];
