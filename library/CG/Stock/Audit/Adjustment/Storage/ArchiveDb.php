<?php
namespace CG\Stock\Audit\Adjustment\Storage;

use CG\Stdlib\CollectionInterface;
use CG\Stdlib\Date;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Audit\Adjustment\Collection;
use CG\Stock\Audit\Adjustment\ConvertibleMigrationInterface;
use CG\Stock\Audit\Adjustment\MigrationPeriod;
use CG\Stock\Audit\Adjustment\MigrationTimer;
use CG\Stock\Audit\Adjustment\RawDataCollection;
use Zend\Db\Adapter\Driver\Mysqli\Statement as MysqliStatement;
use Zend\Db\Sql\Select;

class ArchiveDb extends Db implements ConvertibleMigrationInterface
{
    const TABLE = parent::TABLE . 'Archive';
    const LOG_CODE = parent::LOG_CODE . '::Archive';

    public function saveCollection(CollectionInterface $collection, MigrationTimer $migrationTimer = null)
    {
        $timer = $migrationTimer ? $migrationTimer->getUploadTimer() : function() {};
        try {
            return parent::saveCollection($collection);
        } finally {
            $timer();
        }
    }

    public function fetchMigrationPeriodsWithConvertibleDataOlderThanOrEqualTo(Date $date, int $limit = null): array
    {
        $dates = $this->getSelect()->quantifier(Select::QUANTIFIER_DISTINCT)->columns(['date'])->order(['date']);
        $dates->where
            ->lessThanOrEqualTo('date', $date->getDate())
            ->isNotNull('referenceSku')
            ->isNotNull('referenceQuantity');

        /** @var Select $select */
        $select = $this->getReadSql()->select(['tbl' => $dates]);
        $select->columns([
            'from' => 'date',
            'to' => 'date',
        ])->order(['from', 'to'])->group(['from', 'to']);

        $select->having->isNotNull('from')->and->isNotNull('to');
        if ($limit !== null) {
            $select->limit($limit);
        }

        $migrationPeriods = [];
        foreach ($this->getReadSql()->prepareStatementForSqlObject($select)->execute() as $result) {
            $migrationPeriods[] = new MigrationPeriod(
                new Date($result['from']),
                new Date($result['to']),
                static::BULK_DELETE_BATCH_SIZE
            );
        }
        return $migrationPeriods;
    }

    public function fetchConvertibleCollectionForMigrationPeriod(MigrationPeriod $migrationPeriod): Collection
    {
        $select = $this->getSelect()->order('date')->limit($migrationPeriod->getBatchLimit());
        [$from, $to] = [$migrationPeriod->getFrom()->getDate(), $migrationPeriod->getTo()->getDate()];

        if ($from !== $to) {
            $select->where->between('date', $from, $to);
        } else {
            $select->where->equalTo('date', $from);
        }

        $select->where
            ->isNotNull('referenceSku')
            ->isNotNull('referenceQuantity');

        $results = $this->getWriteSql()->prepareStatementForSqlObject(
            $select,
            (new MysqliStatement(false))
                ->setDriver($this->getWriteSql()->getAdapter()->getDriver())
                ->initialize($this->getWriteSql()->getAdapter()->getDriver()->getConnection()->getResource())
        )->execute();

        $collection = new RawDataCollection($this->getMapper(), iterator_to_array($results));
        if ($collection->count() === 0) {
            throw new NotFound('No results match query');
        }

        return $collection;
    }

}