<?php
namespace CG\Stripe;

class UsageRecord
{
    /** @var string */
    protected $id;
    /** @var string */
    protected $subscriptionId;
    /** @var int */
    protected $quantity;
    /** @var int */
    protected $timestamp;

    public function __construct(string $id, string $subscriptionId, int $quantity, int $timestamp)
    {
        $this->id = $id;
        $this->subscriptionId = $subscriptionId;
        $this->quantity = $quantity;
        $this->timestamp = $timestamp;
    }

    public static function fromJson(\stdClass $json)
    {
        return new static(
            $json->id,
            $json->subscription_item,
            $json->quantity,
            $json->timestamp
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSubscriptionId(): string
    {
        return $this->subscriptionId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
}