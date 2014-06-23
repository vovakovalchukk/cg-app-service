<?php
namespace CG\Settings\Invoice\Storage\Persistent;

use CG\Settings\Invoice\Entity;
use CG\Settings\Invoice\Mapper;
use CG\Settings\Invoice\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\PaginatedCollection as Collection;

class Mongo implements StorageInterface
{
    protected $client;
    protected $mapper;

    const MONGO_DATABASE = "settings";
    const MONGO_COLLECTION = "invoice";

    public function __construct(\MongoClient $client, Mapper $mapper)
    {
        $this->setClient($client)
             ->setMapper($mapper);
    }

    public function fetchCollectionByPagination($limit, $page)
    {
        try {
            $query = [];
            $templates = $this->getMongoCollection()->find($query);
            if ($limit != 'all') {
                $offset = ($page - 1) * $limit;
                $templates->limit($limit)->skip($offset);
            }
            if (!$templates->count(true)) {
                throw new NotFound();
            }
            $collection = new Collection($this->getEntityClass(), __FUNCTION__, compact('limit', 'page'));
            $collection->setTotal($templates->count());
            foreach ($templates as $template) {
                $collection->attach($this->getMapper()->fromMongoArray($template));
            }
            return $collection;
        } catch (\MongoException $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function fetch($id)
    {
        try {
            $query = array("_id" =>  (int)$id);
            $invoice = $this->getMongoCollection()->find($query);
            if (!$invoice->count(true)) {
                throw new NotFound(print_r($query, true));
            }
            $invoice->rewind();
            return $this->getMapper()->fromMongoArray($invoice->current());
        } catch (\MongoException $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function save($entity)
    {
        try {
            $order = $this->getMapper()->toMongoArray($entity);
            $save = $this->getMongoCollection()->save($order);
            if (!$save["updatedExisting"]) {
                $entity->setNewlyInserted(true);
            }
        } catch(\MongoException $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function remove($entity)
    {
        try {
            $query = array("_id" => (int)$entity->getId());
            $remove = $this->getMongoCollection()->remove($query);
            if (!$remove["n"]) {
                throw new NotFound();
            }
        } catch(\MongoException $e) {
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

    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        return $this->mapper;
    }

    public function getEntityClass()
    {
        return Entity::class;
    }
}