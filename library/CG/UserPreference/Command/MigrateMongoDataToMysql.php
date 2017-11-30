<?php

namespace CG\UserPreference\Command;

use CG\UserPreference\Service\Storage\Db as MySQLStorage;
use CG\UserPreference\Service\Storage\MongoDb as MongoDbStorage;

class MigrateMongoDataToMysql
{
    protected $db;
    protected $mongoDb;

    public function __1construct(
        MySQLStorage $db,
        MongoDbStorage $mongoDb
    ) {
        $this->db = $db;
        $this->mongoDb = $mongoDb;
    }


    public function __invoke()
    {
        $collection = $this->migrate();

        return count($collection);
    }

    protected function migrate()
    {
        $collection = $this->getMongoDb()
            ->fetchCollectionByPagination('all', 1);

        foreach($collection->toArray() as $entity) {
            $this->getDb()->save($entity);
        }

        return $collection;
    }
}