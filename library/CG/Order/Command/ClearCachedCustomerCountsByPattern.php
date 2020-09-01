<?php
namespace CG\Order\Command;

use CG\Order\Shared\CustomerCounts\Storage\Cache as CustomerCountsCacheStorage;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Predis\Client as PredisClient;
use Predis\Collection\Iterator\HashKey as HashKeyIterator;

class ClearCachedCustomerCountsByPattern implements LoggerAwareInterface
{
    use LogTrait;

    protected const LOG_CODE = 'ClearCachedCustomerCountsByPattern';
    protected const BATCH_LIMIT = 1000;

    /** @var PredisClient */
    protected $predisClient;

    public function __construct(PredisClient $predisClient)
    {
        $this->predisClient = $predisClient;
    }

    public function __invoke(string $pattern, array $organisationUnitIds): void
    {
        foreach ($organisationUnitIds as $organisationUnitId) {
            $this->clearByPatternForOrganisationUnitId($organisationUnitId, $pattern);
        }
    }

    protected function clearByPatternForOrganisationUnitId(int $organisationUnitId, string $pattern): void
    {
        $organisationUnitKey = $this->getKeyForOrganisationUnitId($organisationUnitId);
        foreach ($this->getBatchOfCustomerCountKeys($organisationUnitKey, $pattern) as $batch) {
            $this->clearBatch($organisationUnitKey, $batch);
        }
    }

    protected function getBatchOfCustomerCountKeys(string $organisationUnitKey, string $pattern): \Generator
    {
        $batch = [];
        $count = 0;
        foreach (new HashKeyIterator($this->predisClient, $organisationUnitKey, $pattern) as $customer => $customerCount) {
            $count++;
            $batch[] = $customer;
            if ($count == static::BATCH_LIMIT) {
                yield $batch;
                $count = 0;
                $batch = [];
            }
        }
        yield $batch;
    }

    protected function clearBatch(string $organisationUnitKey, array $batch): void
    {
        if (empty($batch)) {
            return;
        }
        $this->predisClient->hdel($organisationUnitKey, ...$batch);
    }

    protected function getKeyForOrganisationUnitId(int $organisationUnitId): string
    {
        return CustomerCountsCacheStorage::KEY_PREFIX . $organisationUnitId;
    }
}