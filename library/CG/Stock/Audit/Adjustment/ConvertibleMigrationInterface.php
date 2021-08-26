<?php
namespace CG\Stock\Audit\Adjustment;

use CG\Stdlib\Date;

interface ConvertibleMigrationInterface extends MigrationInterface
{
    public function fetchMigrationPeriodsWithConvertibleDataOlderThanOrEqualTo(Date $date, int $limit = null): array;
    public function fetchConvertibleCollectionForMigrationPeriod(MigrationPeriod $migrationPeriod): Collection;
}