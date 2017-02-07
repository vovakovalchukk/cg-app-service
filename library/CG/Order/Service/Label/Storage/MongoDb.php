<?php
namespace CG\Order\Service\Label\Storage;

use CG\Order\Shared\Label\Collection;
use CG\Order\Shared\Label\Entity;
use CG\Order\Shared\Label\Filter;
use CG\Order\Shared\Label\Mapper;
use CG\Order\Shared\Label\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\MongoDb\FetchTrait;
use MongoClient;
use MongoException;

class MongoDb implements StorageInterface
{
    use FetchTrait;

    protected $client;
    protected $mapper;

    const MONGO_DATABASE = "order";
    const MONGO_COLLECTION = "label";

    public function __construct(MongoClient $client, Mapper $mapper)
    {
        $this->setClient($client)
            ->setMapper($mapper);
    }

    public function save($entity)
    {
        try {
            $label = $this->getMapper()->toMongoArray($entity);
            $save = $this->getMongoCollection()->save($label);
            if (!$save["updatedExisting"]) {
                $entity->setNewlyInserted(true);
            }
            return $entity;
        } catch (MongoException $e) {
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
        } catch (MongoException $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $query = [];
            if ($filter->getId()) {
                // Must not pass associative arrays to $in as it treats them as a hash but requires a true array
                $query["_id"] = ['$in' => array_values($filter->getId())];
            }
            if ($filter->getOrganisationUnitId()) {
                $query["organisationUnitId"] = ['$in' => array_map("intval", array_values($filter->getOrganisationUnitId()))];
            }
            if ($filter->getOrderId()) {
                $query["orderId"] = ['$in' => array_values($filter->getOrderId())];
            }
            if ($filter->getStatus()) {
                $query["status"] = ['$in' => array_values($filter->getStatus())];
            }
            if ($filter->getShippingAccountId()) {
                $query["shippingAccountId"] = ['$in' => array_map("intval", array_values($filter->getShippingAccountId()))];
            }
            if ($filter->getCreatedFrom() || $filter->getCreatedTo()) {
                $query["created"] = [];
                if ($filter->getCreatedFrom()) {
                    $query["created"]['$gte'] = $filter->getCreatedFrom();
                }
                if ($filter->getCreatedTo()) {
                    $query["created"]['$lte'] = $filter->getCreatedTo();
                }
            }
            if ($filter->getShippingServiceCode()) {
                $query["shippingServiceCode"] = ['$in' => array_map("intval", array_values($filter->getShippingServiceCode()))];
            }

            $results = $this->getMongoCollection()->find($query);
            $limit = $filter->getLimit();
            $page = $filter->getPage();
            if ($limit != 'all') {
                $offset = ($page - 1) * $limit;
                $results->limit((int) $limit)->skip($offset);
            }
            if (!$results->count(true)) {
                throw new NotFound();
            }
            $collection = new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray());
            $collection->setTotal($results->count());
            foreach ($results as $label) {
                $collection->attach($this->getMapper()->fromMongoArray($label));
            }
            return $collection;
        } catch (MongoException $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function getMongoCollection()
    {
        $database = static::MONGO_DATABASE;
        $collection = static::MONGO_COLLECTION;
        return $this->getClient()->$database->$collection;
    }

    public function setClient(MongoClient $client)
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
        return Entity::class;
    }

    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        return $this->mapper;
    }
}
