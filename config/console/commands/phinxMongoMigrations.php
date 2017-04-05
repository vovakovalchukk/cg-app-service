<?php
use CG\Settings\Invoice\Service\Storage\MongoDb as InvoiceSettingsMongoDb;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Di\Di;

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
];
