<?php
namespace CG\Order\Service\UserChange\Storage;

use CG\Order\Shared\UserChange\Collection as UserChangeCollection;
use CG\Order\Shared\UserChange\Entity as UserChangeEntity;
use CG\Order\Shared\UserChange\Mapper as UserChangeMapper;
use CG\Order\Shared\UserChange\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\MongoDb\FetchTrait;

class MongoDb implements StorageInterface
{
    use FetchTrait;

    protected $client;
    protected $mapper;

    const MONGO_DATABASE = "order";
    const MONGO_COLLECTION = "userChange";

    public function __construct(\MongoClient $client, UserChangeMapper $mapper)
    {
        $this->setClient($client)
            ->setMapper($mapper);
    }

    public function save($entity)
    {
        try {
            $userChange = $entity->toArray();
            $userChange["_id"] = $entity->getOrderId();
            $save = $this->getMongoCollection()->save($userChange);
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
            $query = array("_id" => $entity->getOrderId());
            $remove = $this->getMongoCollection()->remove($query);
            if (!$remove["n"]) {
                throw new NotFound();
            }
        } catch(\MongoException $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }
    
    public function fetchCollectionByOrderIds(array $orderIds)
    {
        try {
            $query["_id"] = array('$in' => array_values($orderIds));
            $userChanges = $this->getMongoCollection()->find($query);
            if (!$userChanges->count(true)) {
                throw new NotFound();
            }
            $collection = new UserChangeCollection($this->getEntityClass(), __FUNCTION__, compact('orderIds'));
            $collection->setTotal($userChanges->count());
            foreach ($userChanges as $userChange) {
                $collection->attach($this->getMapper()->fromArray($userChange));
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
        return OrderEntity::class;
    }

    public function setMapper(UserChangeMapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        return $this->mapper;
    }
}