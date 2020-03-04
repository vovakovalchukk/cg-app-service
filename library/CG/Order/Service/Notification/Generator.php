<?php
namespace CG\Order\Service\Notification;

use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Entity as OrderItem;

class Generator implements GeneratorInterface
{
    public function generateNotificationsForOrder(Order $order, Order $previous = null): iterable
    {
        yield $this->createNotification(
            $order->getId(),
            $order->getAccountId(),
            $order->getOrganisationUnitId()
        );
    }

    public function generateNotificationsForOrderItem(OrderItem $orderItem, OrderItem $previous = null): iterable
    {
        yield $this->createNotification(
            $orderItem->getOrderId(),
            $orderItem->getAccountId(),
            $orderItem->getOrganisationUnitId()
        );
    }

    protected function createNotification(string $orderId, int $accountId, int $ouId): Entity
    {
        return new Entity($orderId, $accountId, $ouId);
    }
}