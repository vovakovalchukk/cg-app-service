<?php
namespace CG\Stripe\Response;

use CG\Stripe\Product\Plan;
use CG\Stripe\ResponseInterface;
use CG\Stripe\Subscription;
use CG\Stripe\Subscription\Item;

class GetSubscriptions implements ResponseInterface
{
    /** @var Subscription[] */
    protected $subscriptions;
    /** @var bool */
    protected $hasMore;

    public function __construct(array $subscriptions, bool $hasMore)
    {
        $this->subscriptions = $subscriptions;
        $this->hasMore = $hasMore;
    }

    public static function fromJson(\stdClass $json): ResponseInterface
    {
        $subscriptions = [];
        foreach ($json->data as $subscriptionJson) {
            $subscriptions[] = static::subscriptionFromJson($subscriptionJson);
        }
        return new static($subscriptions, $json->has_more ?? false);
    }

    protected static function subscriptionFromJson(\stdClass $json): Subscription
    {
        $items = [];
        foreach ($json->items->data as $itemJson) {
            $items[] = static::itemFromJson($itemJson);
        }
        return Subscription::fromJson($json, $items);
    }

    protected static function itemFromJson(\stdClass $json): Item
    {
        $plan = Plan::fromJson($json->plan);
        return Item::fromJson($json, $plan);
    }

    /**
     * @return Subscription[]
     */
    public function getSubscriptions(): array
    {
        return $this->subscriptions;
    }

    public function hasMore(): bool
    {
        return $this->hasMore;
    }
}