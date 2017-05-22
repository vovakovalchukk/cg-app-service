<?php
namespace CG\PurchaseOrder\Item;

use CG\PurchaseOrder\Item\Nginx\Cache\Invalidator as NginxCacheInvalidator;
use Zend\EventManager\GlobalEventManager as EventManager;

class RestService extends Service
{
    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    /** @var EventManager $eventManager */
    protected $eventManager;
    /** @var NginxCacheInvalidator */
    protected $nginxCacheInvalidator;

    public function __construct(
        EventManager $eventManager,
        StorageInterface $repository,
        Mapper $mapper,
        NginxCacheInvalidator $nginxCacheInvalidator
    ) {
        parent::__construct($repository, $mapper);
        $this->eventManager = $eventManager;
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
            $collection, "/purchaseOrderItem", $filter->getLimit(), $filter->getPage(), $filter->toArray()
        );
    }

    public function save($entity)
    {
        $savedEntity = parent::save($entity);
        $this->nginxCacheInvalidator->invalidatePurchaseOrderForItem($entity);
        return $savedEntity;
    }

    public function remove(Entity $entity)
    {
        parent::remove($entity);
        $this->nginxCacheInvalidator->invalidatePurchaseOrderForItem($entity);
    }

    /**
     * Required by PatchTrait
     * @return EventManager
     */
    protected function getEventManager()
    {
        return $this->eventManager;
    }
}
