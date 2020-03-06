<?php
namespace CG\Order\Service\Notification;

use CG\Notification\Notifiable\JsonTrait;
use CG\Notification\NotifiableInterface;
use CG\Notification\Type\Order;

class Entity implements NotifiableInterface
{
    use JsonTrait {
        jsonSerialize as protected jsonSerializeTrait;
    }

    /** @var string */
    protected $orderId;
    /** @var int */
    protected $accountId;
    /** @var int */
    protected $ouId;

    public function __construct(string $orderId, int $accountId, int $ouId)
    {
        $this->orderId = $orderId;
        $this->accountId = $accountId;
        $this->ouId = $ouId;
    }

    public function getNotificationId(): string
    {
        return $this->orderId;
    }

    public function getOrganisationUnitId(): int
    {
        return $this->ouId;
    }

    public function getNotificationType(): string
    {
        return Order::TYPE;
    }

    public function jsonSerialize()
    {
        $jsonData = $this->jsonSerializeTrait();
        $jsonData['accountId'] = $this->accountId;
        return $jsonData;
    }
}