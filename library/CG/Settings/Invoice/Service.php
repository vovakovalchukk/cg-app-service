<?php
// todo find me a home
namespace CG\Settings\Invoice;

use CG\Settings\Invoice\StorageInterface;
use CG\Settings\Invoice\Mapper;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\ServiceTrait;

class Service implements LoggerAwareInterface
{
    use LogTrait;
    use ServiceTrait {
        save as traitSave;
    }

    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    const SAVE_METHOD_CALLED = 'Save method called';
    const ENTITY_EXISTS = 'Entity already exists. Updating';
    const ENTITY_NOT_EXISTS = 'Entity doesn\'t exist. Inserting';

    public function __construct(StorageInterface $repository, Mapper $mapper)
    {
        $this->setRepository($repository)
             ->setMapper($mapper);
    }

    public function fetchCollectionByPagination($limit, $page)
    {
        $limit = $limit ?: static::DEFAULT_LIMIT;
        $page = $page ?: static::DEFAULT_PAGE;

        $collection = $this->getRepository()->fetchCollectionByPagination($limit, $page);

        return $this->getMapper()->collectionToHal(
            $collection,
            "/settings/invoice",
            $limit,
            $page
        );

    }

    public function save($entity)
    {
        $this->logDebug(static::SAVE_METHOD_CALLED, $entity->getId());
        try {
            $existingEntity = $this->getRepository()->fetch($entity->getId());
        } catch (\Exception $e) {
            $this->logInfo(static::ENTITY_NOT_EXISTS, $entity->getId());
            return $this->traitSave($entity);
        }
        $this->logInfo(static::ENTITY_EXISTS, $entity->getId());
        return $this->traitSave($existingEntity);
    }

    public function remove(Entity $entity)
    {
        $this->getRepository()->remove($entity);
        return $this;
    }

    public function setRepository(StorageInterface $repository)
    {
        $this->repository = $repository;
        return $this;
    }

    public function getRepository()
    {
        return $this->repository;
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