<?php
namespace CG\Order\Service\OrderLink;

use CG\Order\Service\OrderLink\Nginx\Cache\Invalidator as NginxCacheInvalidator;
use CG\Order\Shared\OrderLink\Entity;
use CG\Order\Shared\OrderLink\Filter;
use CG\Order\Shared\OrderLink\Mapper;
use CG\Order\Shared\OrderLink\Service;
use CG\Order\Shared\OrderLink\StorageInterface;

class RestService extends Service
{
    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    protected $nginxCacheInvalidator;

    public function __construct(StorageInterface $repository, Mapper $mapper, NginxCacheInvalidator $nginxCacheInvalidator)
    {
        parent::__construct($repository, $mapper);
        $this->nginxCacheInvalidator = $nginxCacheInvalidator;
    }

    public function fetchCollectionByFilterAsHal(Filter $filter)
    {
        if (!$filter->getLimit()) {
            $filter->setLimit(static::DEFAULT_LIMIT);
        }
        if (!$filter->getPage()) {
            $filter->setPage(static::DEFAULT_PAGE);
        }

        $collection = $this->fetchCollectionByFilter($filter);
        return $this->getMapper()->collectionToHal(
            $collection, "/orderLink", $filter->getLimit(), $filter->getPage(), $filter->toArray()
        );
    }

    public function save($entity)
    {
        $savedEntity = parent::save($entity);
        $this->nginxCacheInvalidator->invalidateOrdersForOrderLink($entity);
        return $savedEntity;
    }

    public function remove(Entity $entity)
    {
        parent::remove($entity);
        $this->nginxCacheInvalidator->invalidateOrdersForOrderLink($entity);
    }
}