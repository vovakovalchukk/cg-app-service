<?php
namespace CG\PurchaseOrder\Item;

use CG\PurchaseOrder\Item\Nginx\Cache\Invalidator as NginxCacheInvalidator;
use CG\Stock\Gearman\Generator\AdjustOnPurchaseOrder as AdjustStockOnPurchaseOrderGenerator;
use Zend\EventManager\GlobalEventManager as EventManager;

class RestService extends Service
{
    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    /** @var EventManager $eventManager */
    protected $eventManager;
    /** @var NginxCacheInvalidator */
    protected $nginxCacheInvalidator;
    /** @var AdjustStockOnPurchaseOrderGenerator */
    protected $adjustStockOnPurchaseOrderGenerator;

    public function __construct(
        EventManager $eventManager,
        StorageInterface $repository,
        Mapper $mapper,
        NginxCacheInvalidator $nginxCacheInvalidator,
        AdjustStockOnPurchaseOrderGenerator $adjustStockOnPurchaseOrderGenerator
    ) {
        parent::__construct($repository, $mapper);
        $this->eventManager = $eventManager;
        $this->nginxCacheInvalidator = $nginxCacheInvalidator;
        $this->adjustStockOnPurchaseOrderGenerator = $adjustStockOnPurchaseOrderGenerator;
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
        $fetchedEntity = $entity->getId() ? $this->fetch($entity->getId()) : null;
        $savedEntity = parent::save($entity);
        $this->nginxCacheInvalidator->invalidatePurchaseOrderForItem($entity);
        $this->updateStockOnPurchaseOrderCount($entity, $fetchedEntity);

        return $savedEntity;
    }

    protected function updateStockOnPurchaseOrderCount(Entity $newEntity, ?Entity $existingEntity = null): void
    {
        if ($existingEntity == null) {
            $this->addToStockOnPurchaseOrderCount($newEntity);
            return;
        }
        $this->alterStockOnPurchaseOrderCount($newEntity, $existingEntity);
    }

    protected function addToStockOnPurchaseOrderCount(Entity $entity): void
    {
        $diff = $entity->getQuantity();
        $this->createJobToAlterStockOnPurchaseOrderCount($entity, $diff);
    }

    protected function alterStockOnPurchaseOrderCount(Entity $newEntity, Entity $existingEntity): void
    {
        $diff = $newEntity->getQuantity() - $existingEntity->getQuantity();
        if ($diff == 0) {
            return;
        }
        $this->createJobToAlterStockOnPurchaseOrderCount($newEntity, $diff);
    }

    public function remove(Entity $entity)
    {
        parent::remove($entity);
        $this->nginxCacheInvalidator->invalidatePurchaseOrderForItem($entity);
        $this->subtractFromStockOnPurchaseOrderCount($entity);
    }

    protected function subtractFromStockOnPurchaseOrderCount(Entity $entity): void
    {
        $diff = 0 - $entity->getQuantity();
        $this->createJobToAlterStockOnPurchaseOrderCount($entity, $diff);
    }

    protected function createJobToAlterStockOnPurchaseOrderCount(Entity $entity, int $adjustment): void
    {
        ($this->adjustStockOnPurchaseOrderGenerator)($entity, $adjustment);
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
