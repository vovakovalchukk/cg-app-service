<?php

use CG\Template\Command\MigrateMongoDataToMysql as MigrateMongoTemplateDataToMysql;
use CG\UserPreference\Command\MigrateMongoDataToMysql as MigrateMongoUserPreferenceDataToMysql;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

return [
    'phinx:migrateMongoTemplateDataToMysql' => [
        'command' => function (InputInterface $input, OutputInterface $output) use ($di)
        {
            $output->writeln('Migrating Transaction data from MongoDB to MySQL');
            $command = $di->get(MigrateMongoTemplateDataToMysql::class);
            $count = $command();
            $output->writeln('Finished migration of Templates, ' . $count . ' processed');
        },
        'description' => 'Copies Transaction data over from MongoDB to MySQL',
        'arguments' => [],
        'options' => []
    ],
    'phinx:rollbackMongoTemplateDataFromMysql' => [
        'command' => function (InputInterface $input, OutputInterface $output) use ($di)
        {
            $output->writeln('Migrating Transaction data back from MySQL to MongoDB');
            $command = $di->get(MigrateMongoTemplateDataToMysql::class);
            $count = $command->rollback();
            $output->writeln('Finished rollback of Templates, ' . $count . ' processed');
        },
        'description' => 'Rolls Transaction data back from MySQL to MongoDB',
        'arguments' => [],
        'options' => []
    ],
    'phinx:migrateMongoUserPreferenceDataToMysql' => [
        'command' => function (InputInterface $input, OutputInterface $output) use ($di)
        {
            $output->writeln('Migrating Transaction data from MongoDB to MySQL');
            $command = $di->get(MigrateMongoUserPreferenceDataToMysql::class);
            $count = $command();
            $output->writeln('Finished migration of User Preferences, ' . $count . ' processed');
        },
        'description' => 'Copies Transaction data over from MongoDB to MySQL',
        'arguments' => [],
        'options' => []
    ],
    'phinx:rollbackMongoUserPreferenceDataFromMysql' => [
        'command' => function (InputInterface $input, OutputInterface $output) use ($di)
        {
            $output->writeln('Migrating Transaction data back from MySQL to MongoDB');
            $command = $di->get(MigrateMongoUserPreferenceDataToMysql::class);
            $count = $command->rollback();
            $output->writeln('Finished rollback of User Preferences, ' . $count . ' processed');
        },
        'description' => 'Rolls Transaction data back from MySQL to MongoDB',
        'arguments' => [],
        'options' => []
    ],
];
