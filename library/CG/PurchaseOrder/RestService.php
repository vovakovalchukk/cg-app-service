<?php
namespace CG\PurchaseOrder;

use CG\PurchaseOrder\Item\Filter as ItemFilter;
use CG\PurchaseOrder\Item\Service as ItemService;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\EventManager\GlobalEventManager as EventManager;

class RestService extends Service
{
    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    /** @var EventManager $eventManager */
    protected $eventManager;
    /** @var ItemService */
    protected $itemService;

    public function __construct(
        EventManager $eventManager,
        StorageInterface $repository,
        Mapper $mapper,
        ItemService $itemService
    ) {
        parent::__construct($repository, $mapper);
        $this->eventManager = $eventManager;
        $this->itemService = $itemService;
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
            return $this->itemService->fetchCollectionByFilter($filter);
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

    /**
     * Required by PatchTrait
     * @return EventManager
     */
    protected function getEventManager()
    {
        return $this->eventManager;
    }
}
