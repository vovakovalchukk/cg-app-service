<?php
namespace CG\Stripe\Subscription;

use CG\Stripe\Product\Plan;

class Item
{
    /** @var string */
    protected $id;
    /** @var string */
    protected $subscriptionId;
    /** @var int|null */
    protected $quantity;
    /** @var string */
    protected $created;
    /** @var Plan */
    protected $plan;

    public function __construct(string $id, string $subscriptionId, ?int $quantity, string $created, Plan $plan)
    {
        $this->id = $id;
        $this->subscriptionId = $subscriptionId;
        $this->quantity = $quantity;
        $this->created = $created;
        $this->plan = $plan;
    }

    public static function fromJson(\stdClass $json, Plan $plan): Item
    {
        return new static(
            $json->id,
            $json->subscription,
            $json->quantity ?? null,
            $json->created,
            $plan
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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function getCreated(): string
    {
        return $this->created;
    }

    public function getPlan(): Plan
    {
        return $this->plan;
    }
}