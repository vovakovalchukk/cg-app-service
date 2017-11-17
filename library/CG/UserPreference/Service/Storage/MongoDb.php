<?php
namespace CG\UserPreference\Service\Storage;

use CG\UserPreference\Shared\Collection as UserPreferenceCollection;
use CG\UserPreference\Shared\Entity as UserPreferenceEntity;
use CG\UserPreference\Shared\Mapper as UserPreferenceMapper;
use CG\UserPreference\Shared\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\MongoDb\FetchTrait;

class MongoDb implements StorageInterface
{
    use FetchTrait;

    protected $client;
    protected $mapper;

    const MONGO_DATABASE = "userPreference";
    const MONGO_COLLECTION = "preference";

    public function __construct(\MongoClient $client, UserPreferenceMapper $mapper)
    {
        $this->setClient($client)
            ->setMapper($mapper);
    }

    public function save($entity)
    {
        try {
            $userPreference = $this->getMapper()->toMongoArray($entity);
            $save = $this->getMongoCollection()->save($userPreference);
            if (!$save["updatedExisting"]) {
                $entity->setNewlyInserted(true);
            }
            return $entity;
        } catch(\MongoException $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function remove($entity)
    {
        try {
            $query = array("_id" => $entity->getId());
            $remove = $this->getMongoCollection()->remove($query);
            if (!$remove["n"]) {
                throw new NotFound();
            }
        } catch(\MongoException $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function fetchCollectionByPagination($limit, $page)
    {
        try {
            $userPreferences = $this->getMongoCollection()->find(array());
            if ($limit != 'all') {
                $offset = ($page - 1) * $limit;
                $userPreferences->limit((int) $limit)->skip($offset);
            }
            if (!$userPreferences->count(true)) {
                throw new NotFound();
            }
            $collection = new UserPreferenceCollection($this->getEntityClass(), __FUNCTION__, compact('limit', 'page'));
            $collection->setTotal($userPreferences->count());
            foreach ($userPreferences as $userPreference) {
                $collection->attach($this->getMapper()->fromMongoArray($userPreference));
            }
            return $collection;
        } catch (\MongoException $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function getMongoCollection()
    {
        $database = static::MONGO_DATABASE;
        $collection = static::MONGO_COLLECTION;
        return $this->getClient()->$database->$collection;
    }

    public function setClient(\MongoClient $client)
    {
        $this->client = $client;
        return $this;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getEntityClass()
    {
        return UserPreferenceEntity::class;
    }

    public function setMapper(UserPreferenceMapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        return $this->mapper;
    }
}
