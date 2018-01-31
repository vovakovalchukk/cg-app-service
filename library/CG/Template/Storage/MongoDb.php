<?php
namespace CG\Template\Storage;

use CG\Template\Collection;
use CG\Template\Entity;
use CG\Template\Mapper;
use CG\Template\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Storage\MongoDb\FetchTrait;
use CG\Stdlib\Exception\Storage as StorageException;

class MongoDb implements StorageInterface
{
    use FetchTrait;

    protected $client;
    protected $mapper;

    const MONGO_DATABASE = "template";
    const MONGO_COLLECTION = "template";

    public function __construct(\MongoClient $client, Mapper $mapper)
    {
        $this->client = $client;
        $this->mapper = $mapper;
    }

    public function save($entity)
    {
        return $entity;
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

    public function fetchCollectionByPagination($limit, $page, array $id, array $organisationUnitId, array $type)
    {
        try {
            $query = [];
            if (count($id)) {
                $query["_id"] = ['$in' => $id];
            }

            if (count($organisationUnitId)) {
                $query["organisationUnitId"] = ['$in' => array_map("intval", $organisationUnitId)];
            }

            if (count($type)) {
                $query["type"] = ['$in' => $type];
            }

            $templates = $this->getMongoCollection()->find($query);
            if ($limit != 'all') {
                $offset = ($page - 1) * $limit;
                $templates->limit((int) $limit)->skip($offset);
            }
            if (!$templates->count(true)) {
                throw new NotFound();
            }
            $collection = new Collection(Entity::class, __FUNCTION__, compact('limit', 'page', 'id', 'organisationUnitId', 'type'));
            $collection->setTotal($templates->count());
            foreach ($templates as $template) {
                $collection->attach($this->getMapper()->fromMongoArray($template));
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
        return $this->client->$database->$collection;
    }



    public function getMapper()
    {
        return $this->mapper;
    }
}
