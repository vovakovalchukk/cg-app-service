<?php
namespace CG\Mongo\Migration;

use CG\ETag\Exception\Conflict;
use CG\Order\Service\Filter;
use CG\Order\Service\Item\Service as ItemService;
use CG\Order\Shared\Item\Entity as ItemEntity;
use CG\Order\Shared\Entity as OrderEntity;
use CG\Order\Service\Service as OrderService;

class ItemPurchaseDateStatusMigration
{
    protected $orderService;
    protected $itemService;

    const FIRST_PAGE = 1;
    const ALL_LIMIT = 'all';

    public function __construct(OrderService $orderService, ItemService $itemService)
    {
        $this->setOrderService($orderService)
            ->setItemService($itemService);
    }

    public function migrate()
    {
        $items = $this->getItems();
        foreach ($items as $item) {
            $order = $this->getOrder($item->getOrderId());
            if (!$item->getPurchaseDate() || !$item->getStatus()) {
                $item = $this->migrateItemData($item, $order);
                try {
                    $this->saveItem($item);
                } catch (Conflict $e) {
                    $item = $this->getItemService()->fetch($item->getId());
                    $item = $this->migrateItemData($item, $order);
                    $this->saveItem($item);
                }
            }
        }
    }

    protected function migrateItemData(ItemEntity $item, OrderEntity $order)
    {
        if (!$item->getPurchaseDate()) {
            $item->setPurchaseDate($order->getPurchaseDate());
        }
        if (!$item->getStatus()) {
            $item->setStatus($order->getStatus());
        }
        return $item;
    }

    protected function getItems()
    {
        return $this->getItemService()->fetchCollectionByPagination(static::ALL_LIMIT, static::FIRST_PAGE, []);
    }

    protected function getOrder($orderId)
    {
        if (isset($this->orders)) {
            return $this->orders->getById($orderId);
        }
        $this->orders = $this->getOrders();
        return $this->orders->getById($orderId);
    }

    protected function getOrders()
    {
        $filter = new Filter(static::ALL_LIMIT, static::FIRST_PAGE);
        return $this->getOrderService()->fetchCollectionByFilter($filter);
    }

    protected function saveItem(ItemEntity $entity)
    {
        $this->getItemService()->save($entity);
    }

    protected function setItemService(ItemService $itemService)
    {
        $this->itemService = $itemService;
        return $this;
    }

    protected function getItemService()
    {
        return $this->itemService;
    }

    protected function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    protected function getOrderService()
    {
        return $this->orderService;
    }
}