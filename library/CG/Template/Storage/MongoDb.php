<?php
namespace CG\Template\Storage;

use CG\Template\Collection;
use CG\Template\Entity;
use CG\Template\Filter;
use CG\Template\Mapper;
use CG\Template\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Storage\MongoDb\FetchTrait;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Coerce\Id\StringTrait as IDCoersionOverride;

class MongoDb implements StorageInterface
{
    use IDCoersionOverride {
        coerceId as protected coerceStringId;
    }
    use FetchTrait {
        coerceId as protected coerceIdOriginal;
    }

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

    public function coerceId($class, $id)
    {
        return static::coerceStringId($id);
    }

    public function fetchCollectionByPagination($limit, $page, array $id, array $organisationUnitId, array $type): Collection
    {
        $filter = new Filter($limit, $page, $id, $organisationUnitId, $type);
        return $this->fetchCollectionByFilter($filter);
    }

    public function fetchCollectionByFilter(Filter $filter): Collection
    {
        try {
            $query = [];
            if (!empty($filter->getId())) {
                $query["_id"] = ['$in' => $filter->getId()];
            }

            if (!empty($filter->getOrganisationUnitId())) {
                $query["organisationUnitId"] = ['$in' => array_map("intval", $filter->getOrganisationUnitId())];
            }

            if (!empty($filter->getType())) {
                $query["type"] = ['$in' => $filter->getType()];
            }

            $templates = $this->getMongoCollection()->find($query);
            if ($filter->getLimit() != 'all') {
                $offset = ($filter->getPage() - 1) * $filter->getLimit();
                $templates->limit((int) $filter->getLimit())->skip($offset);
            }
            if (!$templates->count(true)) {
                throw new NotFound();
            }
            $collection = new Collection(Entity::class, __FUNCTION__, $filter->toArray());
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
