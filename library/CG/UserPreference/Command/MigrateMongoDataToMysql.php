<?php

namespace CG\UserPreference\Command;

use CG\UserPreference\Service\Storage\Db as MySQLStorage;
use CG\UserPreference\Service\Storage\MongoDb as MongoDbStorage;
use CG\Stdlib\Exception\Runtime\NotFound as NotFoundException;

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
        $entityArray = [];
        $page = 1;
        do {
            try {
                $collection = $this->mongoDb
                    ->fetchCollectionByPagination(100, $page, [], [], []);
                array_merge($entityArray, $collection->toArray());
                $page++;
            } catch (NotFoundException $unused) {
                break;
            }
        } while (true);

        foreach ($entityArray as $entity) {
            $this->db->save($entity);
        }

        return $entityArray;
    }
}