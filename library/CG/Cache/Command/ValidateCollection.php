<?php
namespace CG\Cache\Command;

use CG\Cache\ClientInterface;
use CG\Cache\ClientMapInterface;
use CG\Cache\InvalidationHandler\ValidateCollection as Collection;
use CG\CGLib\Command\SignalHandlerTrait;
use CG\Queue\BlockingInterface as BlockingQueue;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateCollection implements LoggerAwareInterface, StatsAwareInterface
{
    use LogTrait;
    use StatsTrait;
    use SignalHandlerTrait;

    const LOG_CODE_INVALID_JSON = 'Invalid json for ValidateCollection request found on queue';
    const LOG_MSG_INVALID_JSON = 'Invalid json for ValidateCollection request found on queue';
    const LOG_CODE_COLLECTION_KEY_NOT_IN_MAPS = 'Collection key was not found in all invalidation maps, removing collection from cache';
    const LOG_MSG_COLLECTION_KEY_NOT_IN_MAPS = 'Collection key (%s) was not found in %d invalidation maps, removing collection from cache';

    const STAT_KEY = 'infrastructure.cache.validation.collection.%s.%s';
    const STAT_PASSED = 'passed';
    const STAT_FAILED = 'failed';
    const STAT_DELETED = 'deleted';

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
        $this->registerSignalHandler(function (int $signal) {
            if ($this->process === false) {
                throw new AbortException(
                    sprintf('Aborting due to %s signal', $this->signalName($signal))
                );
            }
            $this->process = false;
        }, SIGTERM, SIGINT);

        $queueKey = $this->validationQueue->generateQueueKey();
        $this->lastBatch = $this->processed = 0;

        while ($this->process) {
            $this->process($output, $queueKey, $batchSize, $timeout, $maxProcess);
        }
    }

    protected function process(OutputInterface $output, $queueKey, $batchSize, $timeout, $maxProcess)
    {
        $processingQueueKey = $this->validationQueue->generateProcessingQueueKey();
        $this->logProcessingQueueKey($output, $processingQueueKey);

        $processingQueue = $this->validationQueue->createBlockingProcessingQueue(
            $queueKey,
            $processingQueueKey,
            $timeout
        );

        try {
            foreach ($processingQueue as $collectionValidationJson) {
                try {
                    $this->validateCollection($output, $collectionValidationJson);

                    $this->processed++;
                    if ($this->reachedMaxProcessCount($maxProcess)) {
                        $this->process = false;
                    }

                    $this->flushLogs();
                    if (!$this->process || $this->isBatchFinished($batchSize)) {
                        break;
                    }
                } finally {
                    unset($collectionValidationJson);
                }
            }
        } finally {
            unset($processingQueue);

            if (!$this->didBatchProcess() || !$this->isBatchFinished($batchSize)) {
                $this->process = false;
            }

            $this->lastBatch = $this->processed;
            $this->validationQueue->removeProcessingQueue($processingQueueKey);
            gc_collect_cycles();
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
            $this->logDoesNotExist($output, $collectionKey);
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
            try {
                $this->client->delete($collectionKey);
                $this->logMissingMaps($output, $collectionKey, $missingFromMaps);
            } catch (NotFound $exception) {
                $this->logDoesNotExist($output, $collectionKey);
            }
        } else {
            $this->logValidationPassed($output, $collectionKey);
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

    protected function logDoesNotExist(OutputInterface $output, $collectionKey)
    {
        $output->writeln('<bg=green;options=bold>[DELETED]</>');
        $this->statCollectionKey($collectionKey, static::STAT_DELETED);
    }

    protected function logMissingMaps(OutputInterface $output, $collectionKey, array $missingFromMaps)
    {
        $output->writeln('<bg=red;options=bold>[FAILED]</>');
        foreach ($missingFromMaps as $mapKey) {
            $output->writeln(sprintf(' - <fg=red>%s</>', $mapKey));
        }
        $this->logWarning(static::LOG_MSG_COLLECTION_KEY_NOT_IN_MAPS, ['cacheKey' => $collectionKey, count($missingFromMaps)], static::LOG_CODE_COLLECTION_KEY_NOT_IN_MAPS, ['missingFromMaps' => implode(PHP_EOL, $missingFromMaps)]);
        $this->statCollectionKey($collectionKey, static::STAT_FAILED);
    }

    protected function logValidationPassed(OutputInterface $output, $collectionKey)
    {
        $output->writeln('<bg=green;options=bold>[PASSED]</>');
        $this->statCollectionKey($collectionKey, static::STAT_PASSED);
    }

    protected function statCollectionKey($collectionKey, $outcome)
    {
        $this->statsIncrement(static::STAT_KEY, [$this->mashKey($collectionKey), $outcome]);
    }

    protected function mashKey($collectionKey) {
        if (preg_match('/(?<=^|\:)CG_[^\:]+(?=\:|$)/', $collectionKey, $matches)) {
            return $matches[0];
        }
        if (preg_match('/^[^-:_]+/', $collectionKey, $matches)) {
            return $matches[0];
        }
        return $collectionKey;
    }
}
