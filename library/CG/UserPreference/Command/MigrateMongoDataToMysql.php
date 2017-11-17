<?php

namespace CG\UserPreference\Command;

use CG\UserPreference\Service\Storage\Db as MySQLStorage;
use CG\UserPreference\Service\Storage\MongoDb as MongoDbStorage;
use CG\UserPreference\Shared\Mapper as UserPreferenceMapper;
use CG\UserPreference\Shared\Collection as UserPreferenceCollection;
use CG\UserPreference\Shared\Entity as UserPreferenceEntity;

class MigrateMongoDataToMysql
{
    protected $mapper;
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
            ->fetchCollectionByPagination('all', 1);

        foreach($collection->toArray() as $entity) {
            $this->getDb()->save($entity);
        }
    }

    public function rollback()
    {

    }

    public function getMapper(): UserPreferenceMapper
    {
        return $this->mapper;
    }

    public function setMapper($mapper): MigrateMongoDataToMysql
    {
        $this->mapper = $mapper;
        return $this;
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