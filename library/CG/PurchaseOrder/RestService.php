<?php
namespace CG\PurchaseOrder;

use CG\PurchaseOrder\Entity as PurchaseOrder;
use CG\PurchaseOrder\Item\Filter as ItemFilter;
use CG\PurchaseOrder\Item\Mapper as PurchaseOrderItemMapper;
use CG\PurchaseOrder\Item\RestService as PurchaseOrderItemService;
use CG\PurchaseOrder\Item\Entity as Item;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Gearman\Generator\AdjustOnPurchaseOrder as AdjustStockOnPurchaseOrderGenerator;
use CG\Stock\Gearman\Generator\StockImport as StockImportGearmanJobGenerator;
use Zend\EventManager\GlobalEventManager as EventManager;

class RestService extends Service
{
    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    /** @var EventManager $eventManager */
    protected $eventManager;
    /** @var StockImportGearmanJobGenerator $stockImportGearmanJobGenerator */
    protected $stockImportGearmanJobGenerator;
    /** @var AdjustStockOnPurchaseOrderGenerator */
    protected $adjustStockOnPurchaseOrderGenerator;

    public function __construct(
        StorageInterface $repository,
        Mapper $mapper,
        PurchaseOrderItemService $purchaseOrderItemService,
        PurchaseOrderItemMapper $purchaseOrderItemMapper,
        StockImportGearmanJobGenerator $stockImportGenerator,
        EventManager $eventManager,
        AdjustStockOnPurchaseOrderGenerator $adjustStockOnPurchaseOrderGenerator
    ) {
        parent::__construct(
            $repository,
            $mapper,
            $purchaseOrderItemService,
            $purchaseOrderItemMapper
        );
        $this->eventManager = $eventManager;
        $this->stockImportGearmanJobGenerator = $stockImportGenerator;
        $this->adjustStockOnPurchaseOrderGenerator = $adjustStockOnPurchaseOrderGenerator;
    }

    public function save(PurchaseOrder $entity, array $itemEntities = null)
    {
        $shouldTriggerStockImport = $this->shouldTriggerStockImport($entity);
        if ($itemEntities === null) {
            $savedEntity = parent::save($entity);
        } else {
            $savedEntity = parent::save($entity, $itemEntities);
        }

        if ($shouldTriggerStockImport) {
            $this->triggerStockImportUpdate($entity);
        }

        return $savedEntity;
    }

    public function remove(Entity $entity): void
    {
        // Remove the items first! They need to check things on the PO before we delete that.
        $this->removeItems($entity);
        $this->getRepository()->remove($entity);
    }

    protected function removeItems(Entity $entity): void
    {
        try {
            /** @var Item $item */
            foreach ($this->purchaseOrderItemService->fetchAllByPurchaseOrderIds([$entity->getId()]) as $item) {
                $this->purchaseOrderItemService->remove($item);
            }
        } catch (NotFound $e) {
            // PO items not found, no-op
        }
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
        $this->fetchAndAddEmbedsToCollection($collection);
        return $this->getMapper()->collectionToHal(
            $collection, "/purchaseOrder", $filter->getLimit(), $filter->getPage(), $filter->toArray()
        );
    }

    public function fetchAsHal($id)
    {
        $entity = $this->fetch($id);
        $this->fetchAndAddEmbedsToEntity($entity);

        return $this->getMapper()->toHal($entity);
    }

    protected function fetchAndAddEmbedsToCollection(Collection $collection)
    {
        $items = $this->fetchItemsByPurchaseOrderIds($collection->getIds());
        if (!$items) {
            return;
        }
        foreach ($collection as $purchaseOrder)
        {
            $itemsForPO = $items->getBy('purchaseOrderId', $purchaseOrder->getId());
            $purchaseOrder->setItems($itemsForPO);
        }
    }

    protected function fetchItemsByPurchaseOrderIds(array $ids)
    {
        $filter = (new ItemFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setPurchaseOrderId($ids);
        try {
            return $this->purchaseOrderItemService->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            return null;
        }
    }

    protected function fetchAndAddEmbedsToEntity(Entity $entity)
    {
        $items = $this->fetchItemsByPurchaseOrderIds([$entity->getId()]);
        if (!$items) {
            return;
        }
        $entity->setItems($items);
    }

    protected function shouldTriggerStockImport(PurchaseOrder $entity): bool
    {
        if ($entity->getStatus() !== Status::COMPLETE) {
            return false;
        }

        try {
            /** @var PurchaseOrder $exitingEntity */
            $exitingEntity = $this->getRepository()->fetch($entity->getId());
        } catch (NotFound $e) {
            return true;
        }
        if ($exitingEntity->getStatus() === Status::COMPLETE) {
            return false;
        }

        return true;
    }

    protected function triggerStockImportUpdate(PurchaseOrder $purchaseOrder): void
    {
        /** Generate a job to trigger the stock import from PurchaseOrder */
        $this->stockImportGearmanJobGenerator->generateJob($purchaseOrder);
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
