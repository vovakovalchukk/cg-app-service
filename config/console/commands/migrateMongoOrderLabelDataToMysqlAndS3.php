<?php
use CG\Order\Service\Label\Storage\MetaPlusLabelData as MetaPlusLabelDataStorage;
use CG\Order\Shared\Label\Filter;
use CG\Order\Shared\Label\Mapper;
use CG\Stdlib\Exception\Runtime\NotFound;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

return [
    'ad-hoc:migrateMongoOrderLabelDataToMysqlAndS3' => [
        'description' => 'Adds the mongo OrderLabel metadata to MySQL and the label data to Amazon S3',
        'arguments' => [],
        'options' => [
            'dryrun' => [
                'description' => 'Don\'t actually write the data, just log what would happen',
            ],
        ],
        'command' => function (InputInterface $input, OutputInterface $output) use ($di)
        {
            if ($input->getOption('dryrun')) {
                $output->writeln('<comment>DRY RUN</comment>, not writing to MySQL / S3');
            } else {
                $output->writeln('<info>LIVE RUN</info>, writing to MySQL / S3');
            }

            $mongoClient = $di->get('mongodb');
            $mapper = $di->get(Mapper::class);
            $newStorage = $di->get(MetaPlusLabelDataStorage::class);

            $result = $mongoClient->order->label->find();

            $format = ' %current%/%max% [%bar%] %percent:3s%%';
            $overwrite = true;
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $format = ' %message%' . "\n" . $format;
                $overwrite = false;
            }
            $progress = new ProgressBar($output, $result->count());
            $progress->setMessage('');
            $progress->setFormat($format);
            $progress->setOverwrite($overwrite);
            $progress->start();

            foreach ($result as $labelData) {
                $labelData['mongoId'] = $labelData['_id'];
                unset($labelData['_id']);
                $orderLabel = $mapper->fromArray($labelData);
                $message = sprintf('Migrating %s, OU %s: ', $orderLabel->getMongoId(), $orderLabel->getOrganisationUnitId());
                try {
                    $filter = (new Filter())
                        ->setLimit(1)
                        ->setPage(1)
                        ->setMongoId([$orderLabel->getMongoId()]);
                    $newStorage->fetchCollectionByFilter($filter);
                    $progress->setMessage($message . 'Already in MySQL / S3');
                    $progress->advance();
                    continue;

                } catch (NotFound $ex) {
                    if (!$input->getOption('dryrun')) {
                        $newStorage->save($orderLabel);
                        $progress->setMessage($message . 'Saved to MySQL / S3');
                    }
                    $progress->advance();
                }
            }
            $progress->finish();
            $output->writeln('');
        }
    ]
];
