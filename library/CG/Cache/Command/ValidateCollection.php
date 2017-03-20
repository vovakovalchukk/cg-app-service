<?php
namespace CG\Cache\Command;

use CG\Cache\ClientInterface;
use CG\Cache\ClientMapInterface;
use CG\Cache\InvalidationHandler\ValidateCollection as Collection;
use CG\Queue\BlockingInterface as BlockingQueue;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateCollection implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE_INVALID_JSON = 'Invalid json for ValidateCollection request found on queue';
    const LOG_MSG_INVALID_JSON = 'Invalid json for ValidateCollection request found on queue';
    const LOG_CODE_COLLECTION_KEY_NOT_IN_MAPS = 'Collection key was not found in all invalidation maps, removing collection from cache';
    const LOG_MSG_COLLECTION_KEY_NOT_IN_MAPS = 'Collection key (%s) was not found in %d invalidation maps, removing collection from cache';
    const LOG_CODE_REQUEUE_FAILED_PROCESSING_QUEUE = 'Requeuing a failed processing queue';
    const LOG_MSG_REQUEUE_FAILED_PROCESSING_QUEUE = 'Requeuing failed processing queue %s';

    /** @var BlockingQueue $validationQueue */
    protected $validationQueue;
    /** @var ClientInterface $client */
    protected $client;
    /** @var ClientMapInterface $mapClient */
    protected $mapClient;

    public function __construct(
        BlockingQueue $validationQueue,
        ClientInterface $client,
        ClientMapInterface $mapClient
    ) {
        $this->validationQueue = $validationQueue;
        $this->client = $client;
        $this->mapClient = $mapClient;
    }

    public function processQueue(OutputInterface $output, $batchSize, $timeout = 30, $maxProcess = null)
    {
        $delayLogs = [$this->getLogger(), 'delayLogs'];
        if (is_callable($delayLogs)) {
            $delayLogs(false);
        }
        $this->flushLogs();

        $queueKey = $this->validationQueue->generateQueueKey();

        $process = true;
        if (extension_loaded('pcntl')) {
            $signals = [];
            $signalHandler = function() use(&$process, &$signals) {
                $process = false;
                foreach ($signals as $signal => $handler) {
                    pcntl_signal($signal, $handler);
                }
            };
            foreach ([SIGTERM, SIGINT] as $signal) {
                $signals[$signal] = pcntl_signal_get_handler($signal);
                pcntl_signal($signal, $signalHandler);
            }
        }

        $lastBatch = $processed = 0;
        while ($process) {
            $processingQueueKey = $this->validationQueue->generateProcessingQueueKey();
            $output->writeln(sprintf('Creating processing queue <fg=green>%s</>', $processingQueueKey));

            $processingQueue = $this->validationQueue->createBlockingProcessingQueue(
                $queueKey,
                $processingQueueKey,
                $timeout
            );

            foreach ($processingQueue as $collectionValidationJson) {
                $this->validateCollection($output, $collectionValidationJson);

                $this->flushLogs();
                if (extension_loaded('pcntl')) {
                    pcntl_signal_dispatch();
                }

                $processed++;
                $process = $process && (!$maxProcess || $maxProcess > $processed);
                if (!$process || ($processed % $batchSize) == 0) {
                    break;
                }
            }

            if ($processed == $lastBatch || ($processed % $batchSize) != 0) {
                $process = false;
            }

            $lastBatch = $processed;
            $this->validationQueue->removeProcessingQueue($processingQueueKey);

            if (extension_loaded('pcntl')) {
                pcntl_signal_dispatch();
            }
        }
    }

    protected function validateCollection(OutputInterface $output, $collectionValidationJson)
    {
        $output->write('Validating Collection ');
        try {
            $collectionValidation = Collection::fromJson($collectionValidationJson);
        } catch (\InvalidArgumentException $exception) {
            $output->writeln('<bg=red;options=bold>[INVALID JSON]</>');
            $this->logWarningException(
                $exception,
                static::LOG_MSG_INVALID_JSON,
                [],
                static::LOG_CODE_INVALID_JSON
            );
            $this->flushLogs();
            return;
        }

        $collectionKey = $collectionValidation->getCacheKey();
        $output->write(sprintf('<fg=green>%s</>: ', $collectionKey));
        $missingFromMaps = [];

        foreach ($collectionValidation->getMaps() as $mapKey) {
            try {
                $map = $this->mapClient->setGet($mapKey);
                if (!is_array($map) || empty($map)) {
                    throw new NotFound();
                }
                $mappedKeys = array_flip($map);
                if (!isset($mappedKeys[$collectionKey])) {
                    $missingFromMaps[] = $mapKey;
                }
            } catch (NotFound $exception) {
                // If the map is missing, that means we were not in it!
                $missingFromMaps[] = $mapKey;
            }
        }

        if (!empty($missingFromMaps)) {
            $output->writeln('<bg=red;options=bold>[FAILED]</>');
            foreach ($missingFromMaps as $mapKey) {
                $output->writeln(sprintf(' - <fg=red>%s</>', $mapKey));
            }
            $this->logWarning(
                static::LOG_MSG_COLLECTION_KEY_NOT_IN_MAPS,
                ['cacheKey' => $collectionKey, count($missingFromMaps)],
                static::LOG_CODE_COLLECTION_KEY_NOT_IN_MAPS,
                ['missingFromMaps' => implode(PHP_EOL, $missingFromMaps)]
            );
            $this->client->delete($collectionKey);
        } else {
            $output->writeln('<bg=green;options=bold>[PASSED]</>');
        }
    }

    public function requeueStaleProcessingQueues($age = null)
    {
        $queueKey = $this->validationQueue->generateQueueKey();
        foreach ($this->validationQueue->getStaleProcessingQueues($age) as $staleProcessingQueue) {
            $this->logWarning(static::LOG_MSG_REQUEUE_FAILED_PROCESSING_QUEUE, [$staleProcessingQueue], static::LOG_CODE_REQUEUE_FAILED_PROCESSING_QUEUE);
            $this->validationQueue->requeueProcessingQueue($queueKey, $staleProcessingQueue);
        }
    }
}
