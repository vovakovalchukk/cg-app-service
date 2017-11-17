<?php
namespace CG\UserPreference\Service;

use CG\UserPreference\Shared\StorageInterface as UserPreferenceStorage;
use CG\UserPreference\Shared\Mapper as UserPreferenceMapper;
use CG\UserPreference\Shared\Entity as UserPreferenceEntity;
use CG\Stdlib\ServiceTrait;

class Service
{
    use ServiceTrait;

    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    public function __construct(UserPreferenceStorage $repository, UserPreferenceMapper $mapper)
    {
        $this->setRepository($repository)
            ->setMapper($mapper);
    }

    public function fetchCollectionByPaginationAsHal($limit, $page)
    {
        $limit = $limit ?: static::DEFAULT_LIMIT;
        $page = $page ?: static::DEFAULT_PAGE;

        $collection = $this->getRepository()->fetchCollectionByPagination($limit, $page);
        return $this->getMapper()->collectionToHal($collection, "/userPreference", $limit, $page, array());
    }

    public function remove(UserPreferenceEntity $entity)
    {
        $this->getRepository()->remove($entity);
    }

    public function setRepository(UserPreferenceStorage $repository)
    {
        $this->repository = $repository;
        return $this;
    }

    public function getRepository()
    {
        return $this->repository;
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