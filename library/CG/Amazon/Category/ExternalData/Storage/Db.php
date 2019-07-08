<?php
namespace CG\Amazon\Category\ExternalData\Storage;

use CG\Amazon\Category\ExternalData\Data;
use CG\Amazon\Category\ExternalData\StorageInterface;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

class Db implements StorageInterface
{
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

        $data = $this->readSql->prepareStatementForSqlObject($select)->execute();

        return Data::fromArray($data);
    }

    public function save(int $categoryId, Data $data): void
    {
        $insert = $this->getInsert()->values(['id' => $categoryId, 'data' => json_encode($data->toArray())]);
        $this->readSql->prepareStatementForSqlObject($insert)->execute();
    }

    public function remove(int $categoryId): void
    {
        $this->getDelete()->where(['id' => $categoryId]);
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