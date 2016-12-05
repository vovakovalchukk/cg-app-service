<?php
namespace CG\Settings\Invoice\Service\Storage;

use CG\Settings\Invoice\Shared\Entity;
use CG\Settings\Invoice\Shared\Filter;
use CG\Settings\Invoice\Shared\Mapper;
use CG\Settings\Invoice\Shared\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\PaginatedCollection as Collection;
use CG\Stdlib\Storage\MongoDb\FetchTrait;

class MongoDb implements StorageInterface
{
    use FetchTrait;

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
        return $this->fetchCollectionByFilter(
            new Filter($limit, $page)
        );
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $query = [];

            if ($filter->getEmailSendAs()) {
                $query['emailSendAs'] = $filter->getEmailSendAs();
            }

            if ($filter->getEmailVerified()) {
                $query['emailVerified'] = $filter->getEmailVerified();
            }

            if ($filter->getPendingVerification()) {
                $query['$and'] = [
                    [ '$or' => [ ['emailVerified' => false], ['tradingCompanies.emailVerified' => false] ]],
                    [ '$or' => [ ['emailVerificationStatus' => 'Pending'], ['tradingCompanies.emailVerificationStatus' => 'Pending'] ]],
                ];
            }

            if ($filter->getVerifiedEmail()) {
                $query['$or'] = [ ['emailVerified' => true], ['tradingCompanies.emailVerified' => true] ];
            }

            $limit = $filter->getLimit();
            $page = $filter->getPage();

            $settings = $this->getMongoCollection()->find($query);
            if ($limit != 'all') {
                $offset = ($page - 1) * $limit;
                $settings->limit($limit)->skip($offset);
            }
            if (!$settings->count(true)) {
                throw new NotFound();
            }
            $collection = new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray());
            $collection->setTotal($settings->count());
            foreach ($settings as $setting) {
                $collection->attach($this->getMapper()->fromMongoArray($setting));
            }
            return $collection;
        } catch (\MongoException $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }
    
    public function save($entity)
    {
        try {
            $invoice = $this->getMapper()->toMongoArray($entity);
            $save = $this->getMongoCollection()->save($invoice);
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
