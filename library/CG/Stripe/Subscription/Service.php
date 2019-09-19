<?php
namespace CG\Stripe\Subscription;

use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stripe\Client as StripeClient;
use CG\Stripe\Request\GetSubscriptions as GetSubscriptionsRequest;
use CG\Stripe\Response\GetSubscriptions as GetSubscriptionsResponse;
use CG\Stripe\Subscription;
use CG\Stripe\Subscription\Exception\MultipleActiveSubscriptionsException;

class Service
{
    /** @var StripeClient */
    protected $stripeClient;

    public function __construct(StripeClient $stripeClient)
    {
        $this->stripeClient = $stripeClient;
    }

    public function fetchActiveForCustomer(string $customerId): Subscription
    {
        $subscriptions = $this->fetchCollection($customerId, GetSubscriptionsRequest::STATUS_ACTIVE);
        if (count($subscriptions) > 1) {
            throw new MultipleActiveSubscriptionsException($customerId, $subscriptions);
        }
        return array_pop($subscriptions);
    }

    public function fetchCollection(string $customerId, ?string $status = null): array
    {
        $request = $this->buildGetSubscriptionsRequest($customerId, $status);
        /** @var GetSubscriptionsResponse $response */
        $response = $this->stripeClient->send($request);
        if (empty($response->getSubscriptions())) {
            throw new NotFound();
        }
        return $response->getSubscriptions();
    }

    protected function buildGetSubscriptionsRequest(string $customerId, ?string $status = null): GetSubscriptionsRequest
    {
        return (new GetSubscriptionsRequest())
            ->setCustomer($customerId)
            ->setStatus($status);
    }
}