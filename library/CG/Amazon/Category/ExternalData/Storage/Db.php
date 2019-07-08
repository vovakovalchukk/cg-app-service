<?php
namespace CG\Amazon\Category\ExternalData\Storage;

use CG\Amazon\Category\ExternalData\Data;
use CG\Amazon\Category\ExternalData\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

class Db implements StorageInterface
{
    const NOT_FOUND_MSG = 'Category %d External Details have not been found';
    const TABLE = 'amazonCategoryExternalData';

    /** @var Sql */
    protected $readSql;
    /** @var Sql */
    protected $writeSql;

    public function __construct(Sql $readSql, Sql $writeSql)
    {
        $this->readSql = $readSql;
        $this->writeSql = $writeSql;
    }

    public function fetch(int $categoryId): Data
    {
        $select = $this->getSelect()->where(['id' => $categoryId]);
        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();

        if ($results->count() <= 0) {
            throw new NotFound(sprintf(static::NOT_FOUND_MSG, $categoryId));
        }

        $results->rewind();
        $data = $results->current();

        return Data::fromArray(json_decode($data['data'],1));
    }

    public function save(int $categoryId, Data $data): void
    {
        $insert = $this->getInsert()->values(['id' => $categoryId, 'data' => json_encode($data->toArray())]);
        $this->writeSql->prepareStatementForSqlObject($insert)->execute();
    }

    public function remove(int $categoryId): void
    {
        $delete = $this->getDelete()->where(['id' => $categoryId]);
        $this->writeSql->prepareStatementForSqlObject($delete)->execute();
    }

    protected function getSelect(): Select
    {
        return $this->readSql->select(static::TABLE);
    }

    protected function getInsert(string $table = self::TABLE): Insert
    {
        return $this->writeSql->insert($table);
    }

    protected function getDelete(): Delete
    {
        return $this->writeSql->delete(static::TABLE);
    }
}