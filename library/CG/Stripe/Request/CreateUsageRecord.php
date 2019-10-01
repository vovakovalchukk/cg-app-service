<?php
namespace CG\Stripe\Request;

use CG\Stripe\Response\CreateUsageRecord as Response;

class CreateUsageRecord extends PostAbstract
{
    public const ACTION_INC = 'increment';
    public const ACTION_SET = 'set';

    protected const URI = '/v1/subscription_items/{{subscriptionItemId}}/usage_records';

    /** @var string */
    protected $subscriptionItemId;
    /** @var int */
    protected $quantity;
    /** @var int */
    protected $timestamp;
    /** @var string|null */
    protected $action;

    public function __construct(string $subscriptionId, int $quantity, int $timestamp, ?string $action = null)
    {
        $this->subscriptionItemId = $subscriptionId;
        $this->quantity = $quantity;
        $this->timestamp = $timestamp;
        $this->action = $action;
    }

    public function getUri(): string
    {
        return str_replace('{{subscriptionItemId}}', $this->getSubscriptionItemId(), static::URI);
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    protected function toArray(): array
    {
        return [
            'quantity' => $this->getQuantity(),
            'timestamp' => $this->getTimestamp(),
            'action' => $this->getAction(),
        ];
    }

    public function getSubscriptionItemId(): string
    {
        return $this->subscriptionItemId;
    }

    public function setSubscriptionItemId(string $subscriptionItemId): CreateUsageRecord
    {
        $this->subscriptionItemId = $subscriptionItemId;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): CreateUsageRecord
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function setTimestamp(int $timestamp): CreateUsageRecord
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): CreateUsageRecord
    {
        $this->action = $action;
        return $this;
    }
}