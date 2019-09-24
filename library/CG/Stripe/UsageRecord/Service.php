<?php
namespace CG\Stripe\UsageRecord;

use CG\Stripe\Client as StripeClient;
use CG\Stripe\Request\CreateUsageRecord as CreateUsageRecordRequest;
use CG\Stripe\Response\CreateUsageRecord as CreateUsageRecordResponse;
use CG\Stripe\Subscription\Item as SubscriptionItem;
use CG\Stripe\UsageRecord;
use DateTime;

class Service
{
    /** @var StripeClient */
    protected $stripeClient;

    public function __construct(StripeClient $stripeClient)
    {
        $this->stripeClient = $stripeClient;
    }

    public function increment(int $amount, SubscriptionItem $subscriptionItem, DateTime $date): UsageRecord
    {
        $request = $this->buildIncrementUsageRequest($subscriptionItem, $amount, $date);
        /** @var CreateUsageRecordResponse $response */
        $response = $this->stripeClient->send($request);
        return $response->getUsageRecord();
    }

    protected function buildIncrementUsageRequest(
        SubscriptionItem $subscriptionItem,
        int $amount,
        DateTime $date
    ): CreateUsageRecordRequest {
        return new CreateUsageRecordRequest(
            $subscriptionItem->getId(),
            $amount,
            $date->getTimestamp(),
            CreateUsageRecordRequest::ACTION_INC
        );
    }
}