<?php
namespace CG\Stripe\Subscription\Exception;

use CG\Stripe\Subscription;

class MultipleActiveSubscriptionsException extends \RuntimeException
{
    /** @var Subscription[] */
    protected $subscriptions = [];

    public function __construct(string $customerId, array $subscriptions)
    {
        parent::__construct('Stripe customer ' . $customerId . ' has ' . count($subscriptions) . ' active subscriptions');
        $this->subscriptions = $subscriptions;
    }


    public function getSubscriptions(): array
    {
        return $this->subscriptions;
    }

    public function getSubscriptionIds(): array
    {
        $subscriptionIds = [];
        foreach ($this->getSubscriptions() as $subscription) {
            $subscriptionIds[] = $subscription->getId();
        }
        return $subscriptionIds;
    }
}