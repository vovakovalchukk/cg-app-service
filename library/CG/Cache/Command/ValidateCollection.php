<?php
namespace CG\Cache\Command;

use CG\Cache\ClientInterface;
use CG\Cache\ClientMapInterface;
use CG\Cache\InvalidationHandler\ValidateCollection as Collection;
use CG\Queue\BlockingInterface as BlockingQueue;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Process\PcntlTrait;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateCollection implements LoggerAwareInterface
{
    use LogTrait;
    use PcntlTrait;

    const LOG_CODE_INVALID_JSON = 'Invalid json for ValidateCollection request found on queue';
    const LOG_MSG_INVALID_JSON = 'Invalid json for ValidateCollection request found on queue';
    const LOG_CODE_COLLECTION_KEY_NOT_IN_MAPS = 'Collection key was not found in all invalidation maps, removing collection from cache';
    const LOG_MSG_COLLECTION_KEY_NOT_IN_MAPS = 'Collection key (%s) was not found in %d invalidation maps, removing collection from cache';

    /** @var BlockingQueue $validationQueue */
    protected $validationQueue;
    /** @var ClientInterface $client */
    protected $client;
    /** @var ClientMapInterface $mapClient */
    protected $mapClient;
    /** @var bool $process */
    protected $process;
    /** @var int $processed */
    protected $processed;
    /** @var int $lastBatch */
    protected $lastBatch;

    public function __construct(
        BlockingQueue $validationQueue,
        ClientInterface $client,
        ClientMapInterface $mapClient
    ) {
        $this->validationQueue = $validationQueue;
        $this->client = $client;
        $this->mapClient = $mapClient;
    }

    public function __invoke(OutputInterface $output, $batchSize, $timeout = 30, $maxProcess = null)
    {
        $delayLogs = [$this->getLogger(), 'delayLogs'];
        if (is_callable($delayLogs)) {
            $delayLogs(false);
        }
        $this->flushLogs();

        $this->process = true;
        $this->registerSignalHandler(
            [SIGTERM, SIGINT],
            function() {
                $this->process = false;
            },
            true
        );

        $queueKey = $this->validationQueue->generateQueueKey();
        $this->lastBatch = $this->processed = 0;

        while ($this->process) {
            $processingQueueKey = $this->validationQueue->generateProcessingQueueKey();
            $this->logProcessingQueueKey($output, $processingQueueKey);

            $processingQueue = $this->validationQueue->createBlockingProcessingQueue(
                $queueKey,
                $processingQueueKey,
                $timeout
            );

            foreach ($processingQueue as $collectionValidationJson) {
                $this->validateCollection($output, $collectionValidationJson);

                $this->processed++;
                if ($this->reachedMaxProcessCount($maxProcess)) {
                    $this->process = false;
                }

                $this->flushLogs();
                $this->dispatchSignals();

                if (!$this->process || $this->isBatchFinished($batchSize)) {
                    break;
                }
            }

            if (!$this->didBatchProcess() || !$this->isBatchFinished($batchSize)) {
                $this->process = false;
            }

            $this->lastBatch = $this->processed;
            $this->validationQueue->removeProcessingQueue($processingQueueKey);
            $this->dispatchSignals();
        }
    }

    protected function reachedMaxProcessCount($maxProcess)
    {
        return $maxProcess && $this->processed >= $maxProcess;
    }

    protected function isBatchFinished($batchSize)
    {
        return ($this->processed % $batchSize) == 0;
    }

    protected function didBatchProcess()
    {
        return $this->processed > $this->lastBatch;
    }

    protected function validateCollection(OutputInterface $output, $collectionValidationJson)
    {
        $this->logValidationStarted($output);
        try {
            $collectionValidation = Collection::fromJson($collectionValidationJson);
        } catch (\InvalidArgumentException $exception) {
            $this->logInvalidJson($output, $exception);
            return;
        }

        $collectionKey = $collectionValidation->getCacheKey();
        $this->logCollectionKey($output, $collectionKey);

        if (!$this->client->exists($collectionKey)) {
            $this->logDoesNotExist($output);
            return;
        }

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
            $this->logMissingMaps($output, $collectionKey, $missingFromMaps);
            $this->client->delete($collectionKey);
        } else {
            $this->logValidationPassed($output);
        }
    }

    protected function logProcessingQueueKey(OutputInterface $output, $processingQueueKey)
    {
        $output->writeln(sprintf('Creating processing queue <fg=green>%s</>', $processingQueueKey));
    }

    protected function logValidationStarted(OutputInterface $output)
    {
        $output->write('Validating Collection ');
    }

    protected function logInvalidJson(OutputInterface $output, \InvalidArgumentException $exception)
    {
        $output->writeln('<bg=red;options=bold>[INVALID JSON]</>');
        $this->logWarningException($exception, static::LOG_MSG_INVALID_JSON, [], static::LOG_CODE_INVALID_JSON);
        $this->flushLogs();
    }

    protected function logCollectionKey(OutputInterface $output, $collectionKey)
    {
        $output->write(sprintf('<fg=green>%s</>: ', $collectionKey));
    }

    protected function logDoesNotExist(OutputInterface $output)
    {
        $output->writeln('<bg=green;options=bold>[DELETED]</>');
    }

    protected function logMissingMaps(OutputInterface $output, $collectionKey, array $missingFromMaps)
    {
        $output->writeln('<bg=red;options=bold>[FAILED]</>');
        foreach ($missingFromMaps as $mapKey) {
            $output->writeln(sprintf(' - <fg=red>%s</>', $mapKey));
        }
        $this->logWarning(static::LOG_MSG_COLLECTION_KEY_NOT_IN_MAPS, ['cacheKey' => $collectionKey, count($missingFromMaps)], static::LOG_CODE_COLLECTION_KEY_NOT_IN_MAPS, ['missingFromMaps' => implode(PHP_EOL, $missingFromMaps)]);
    }

    protected function logValidationPassed(OutputInterface $output)
    {
        $output->writeln('<bg=green;options=bold>[PASSED]</>');
    }
}
