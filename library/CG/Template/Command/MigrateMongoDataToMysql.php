<?php

namespace CG\Template\Command;

use CG\Template\Storage\Db as MySQLStorage;
use CG\Template\Storage\MongoDb as MongoDbStorage;

class MigrateMongoDataToMysql
{
    protected $db;
    protected $mongoDb;

    public function __construct(
        MySQLStorage $db,
        MongoDbStorage $mongoDb
    ) {

    }

    public function __invoke()
    {
        $collection = $this->migrate();

        return count($collection);
    }

    protected function migrate()
    {
        $collection = $this->getMongoDb()
            ->fetchCollectionByPagination('all', 1, [], [], []);

        foreach($collection->toArray() as $entity) {
            $this->getDb()->save($entity);
        }

        return $collection;
    }

    public function getDb(): MySQLStorage
    {
        return $this->db;
    }

    public function setDb(MySQLStorage $db): MigrateMongoDataToMysql
    {
        $this->db = $db;
        return $this;
    }

    public function getMongoDb(): MongoDbStorage
    {
        return $this->mongoDb;
    }

    public function setMongoDb(MongoDbStorage $mongoDb): MigrateMongoDataToMysql
    {
        $this->mongoDb = $mongoDb;
        return $this;
    }
}