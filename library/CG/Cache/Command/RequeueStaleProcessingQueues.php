<?php
namespace CG\Cache\Command;

use CG\Queue\BlockingInterface as BlockingQueue;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class RequeueStaleProcessingQueues implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE_REQUEUE_FAILED_PROCESSING_QUEUE = 'Requeuing a failed processing queue';
    const LOG_MSG_REQUEUE_FAILED_PROCESSING_QUEUE = 'Requeuing failed processing queue %s';

    /** @var BlockingQueue $validationQueue */
    protected $validationQueue;

    public function __construct(BlockingQueue $validationQueue)
    {
        $this->validationQueue = $validationQueue;
    }

    public function __invoke($age = null)
    {
        $queueKey = $this->validationQueue->generateQueueKey();
        foreach ($this->validationQueue->getStaleProcessingQueues($age) as $staleProcessingQueue) {
            $this->logWarning(static::LOG_MSG_REQUEUE_FAILED_PROCESSING_QUEUE, [$staleProcessingQueue], static::LOG_CODE_REQUEUE_FAILED_PROCESSING_QUEUE);
            $this->validationQueue->requeueProcessingQueue($queueKey, $staleProcessingQueue);
        }
    }
}
