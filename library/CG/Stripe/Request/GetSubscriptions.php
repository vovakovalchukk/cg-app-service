<?php
namespace CG\Stripe\Request;

use CG\Stripe\Response\GetSubscriptions as Response;

class GetSubscriptions extends GetAbstract
{
    public const STATUS_INCOMPLETE = 'incomplete';
    public const STATUS_INCOMPLETE_EXPIRED = 'incomplete_expired';
    public const STATUS_TRIALING = 'trialing';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_CANCELLED = 'canceled';
    public const STATUS_ALL = 'all';

    protected const URI = '/v1/subscriptions';
    protected const MAX_LIMIT = 100;

    /** @var int|null */
    protected $limit;
    /** @var string|null */
    protected $startingAfter;
    /** @var string|null */
    protected $endingBefore;
    /** @var string|null */
    protected $customer;
    /** @var string|null */
    protected $status;
    /** @var string|null */
    protected $plan;

    protected function getUriWithoutQuery(): string
    {
        return static::URI;
    }

    protected function toArray(): array
    {
        return [
            'limit' => $this->getLimit() ?? static::MAX_LIMIT,
            'starting_after' => $this->getStartingAfter(),
            'ending_before' => $this->getEndingBefore(),
            'customer' => $this->getCustomer(),
            'status' => $this->getStatus(),
            'plan' => $this->getPlan(),
        ];
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(?int $limit): GetSubscriptions
    {
        $this->limit = $limit;
        return $this;
    }

    public function getStartingAfter(): ?string
    {
        return $this->startingAfter;
    }

    public function setStartingAfter(?string $startingAfter): GetSubscriptions
    {
        $this->startingAfter = $startingAfter;
        return $this;
    }

    public function getEndingBefore(): ?string
    {
        return $this->endingBefore;
    }

    public function setEndingBefore(?string $endingBefore): GetSubscriptions
    {
        $this->endingBefore = $endingBefore;
        return $this;
    }

    public function getCustomer(): ?string
    {
        return $this->customer;
    }

    public function setCustomer(?string $customer): GetSubscriptions
    {
        $this->customer = $customer;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): GetSubscriptions
    {
        $this->status = $status;
        return $this;
    }

    public function getPlan(): ?string
    {
        return $this->plan;
    }

    public function setPlan(?string $plan): GetSubscriptions
    {
        $this->plan = $plan;
        return $this;
    }
}