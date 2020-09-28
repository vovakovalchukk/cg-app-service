<?php
namespace CG\Stock\Audit\Adjustment;

class MigrationProgress
{
    /** @var int */
    protected $resultsFound;
    /** @var int */
    protected $resultsMigrated;

    public function __construct(int $resultsFound = 0, int $resultsMigrated = 0)
    {
        $this->resultsFound = $resultsFound;
        $this->resultsMigrated = $resultsMigrated;
    }

    public function getResultsFound(): int
    {
        return $this->resultsFound;
    }

    public function getResultsMigrated(): int
    {
        return $this->resultsMigrated;
    }

    public function incrementResultsFound(int $amount): void
    {
        $this->resultsFound += $amount;
    }

    public function incrementResultsMigrated(int $amount): void
    {
        $this->resultsMigrated += $amount;
    }
}