<?php
namespace CG\Stripe\UsageRecord;

use CG\OrganisationUnit\Collection as OrganisationUnitCollection;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stripe\Client as StripeClient;
use CG\Stripe\Product\Plan;
use CG\Stripe\Request\CreateUsageRecord as CreateUsageRecordRequest;
use CG\Stripe\Request\GetSubscriptions as GetSubscriptionsRequest;
use CG\Stripe\Response\GetSubscriptions as GetSubscriptionsResponse;
use CG\Stripe\Subscription;
use CG\Stripe\Subscription\Item as SubscriptionItem;
use CG\Usage\Aggregate\FetchInterface as UsageStorage;
use DateTime;

class Creator implements LoggerAwareInterface
{
    use LogTrait;

    protected const LOG_CODE = 'StripeUsageRecordCreator';
    protected const LOG_START = 'Gathering and sending usage between %s and %s for OUs: %s';
    protected const LOG_NO_STRIPE_ID = 'OU %d does not have a stripeId, will skip';
    protected const LOG_NO_SUBSCRIPTION = 'OU %d does not have a subscription within Stripe, will skip';
    protected const LOG_MULTI_SUBSCRIPTION = 'OU %d has %d subscriptions within Stripe, will notify accounts and skip';
    protected const LOG_SUBSCRIPTION_ERROR = 'There was a problem fetching the Stripe Subscription for OU %d';
    protected const LOG_NO_ORDER_SI = 'OU %d does not have a subscription item within Stripe with usage:orders. This is unexpected. Will skip';
    protected const LOG_USAGE_RECORD_CREATED = 'Created UsageRecord in Stripe for OU %d';
    protected const LOG_USAGE_RECORD_ERROR = 'There was a problem creating the Stripe UsageRecord for OU %d';

    /** @var OrganisationUnitService */
    protected $organisationUnitService;
    /** @var UsageStorage */
    protected $usageStorage;
    /** @var StripeClient */
    protected $stripeClient;
    /** @var string|null */
    protected $accountsEmail;

    public function __construct(
        OrganisationUnitService $organisationUnitService,
        UsageStorage $usageStorage,
        StripeClient $stripeClient,
        ?string $accountsEmail = null
    ) {
        $this->organisationUnitService = $organisationUnitService;
        $this->usageStorage = $usageStorage;
        $this->stripeClient = $stripeClient;
        $this->accountsEmail = $accountsEmail;
    }

    public function __invoke(DateTime $usageFrom = null, DateTime $usageTo = null, int $organisationUnitId = null): void
    {
        $usageFrom = $usageFrom ?? new DateTime('24 hours ago');
        $usageTo = $usageTo ?? new DateTime();
        $this->logDebug(static::LOG_START, [$usageFrom->format(StdlibDateTime::FORMAT), $usageTo->format(StdlibDateTime::FORMAT), $organisationUnitId ?? 'all'], [static::LOG_CODE, 'Start']);

        $rootOus = $this->fetchRootOusToProcess($organisationUnitId);
        /** @var OrganisationUnit $rootOu */
        foreach ($rootOus as $rootOu) {
            $this->addGlobalLogEventParams(['ou' => $rootOu->getId(), 'rootOu' => $rootOu->getId()]);
            $this->sendUsageForOu($rootOu, $usageFrom, $usageTo);
            $this->removeGlobalLogEventParams(['ou', 'rootOu']);
        }
    }

    protected function fetchRootOusToProcess(int $organisationUnitId = null): OrganisationUnitCollection
    {
        if (!$organisationUnitId) {
            return $this->organisationUnitService->fetchRootOus('all', 1);
        }
        /** @var OrganisationUnit $organisationUnit */
        $organisationUnit = $this->organisationUnitService->fetch($organisationUnitId);
        $rootOu = $organisationUnit->getRootEntity();
        $collection = new OrganisationUnitCollection(OrganisationUnit::class, 'fetch', ['id' => $rootOu->getId()]);
        $collection->attach($rootOu);
        return $collection;
    }

    protected function sendUsageForOu(OrganisationUnit $rootOu, DateTime $usageFrom, DateTime $usageTo): void
    {
        if (!$rootOu->getStripeId()) {
            $this->logDebug(static::LOG_NO_STRIPE_ID, [$rootOu->getId()], [static::LOG_CODE, 'NoStripeId']);
            return;
        }
        $subscription = $this->fetchStripeSubscription($rootOu);
        if (!$subscription) {
            return;
        }
        $subscriptionItem = $this->getOrderSubscriptionItem($subscription, $rootOu);
        if (!$subscriptionItem) {
            return;
        }
        $orderCount = $this->getOrderCountFromUsage($rootOu, $usageFrom, $usageTo);
        $this->sendOrderCountToStripe($orderCount, $subscriptionItem, $usageTo, $rootOu);
    }

    protected function fetchStripeSubscription(OrganisationUnit $rootOu): ?Subscription
    {
        try {
            $request = $this->buildGetSubscriptionsRequestForOu($rootOu);
            /** @var GetSubscriptionsResponse $response */
            $response = $this->stripeClient->send($request);
            if (empty($response->getSubscriptions())) {
                throw new NotFound();
            }
            if (count($response->getSubscriptions()) > 1) {
                $this->handleMultipleActiveSubscriptions($rootOu, $response);
                return null;
            }
            $subscriptions = $response->getSubscriptions();
            return array_pop($subscriptions);

        } catch (NotFound $e) {
            $this->logDebug(static::LOG_NO_SUBSCRIPTION, [$rootOu->getId()], [static::LOG_CODE, 'Subscription', 'None']);
            return null;
        } catch (StorageException $e) {
            $this->logAlertException($e, static::LOG_SUBSCRIPTION_ERROR, [$rootOu->getId()], [static::LOG_CODE, 'Subscription', 'Error']);
            return null;
        }
    }

    protected function buildGetSubscriptionsRequestForOu(OrganisationUnit $rootOu): GetSubscriptionsRequest
    {
        return (new GetSubscriptionsRequest())
            ->setCustomer($rootOu->getStripeId())
            ->setStatus(GetSubscriptionsRequest::STATUS_ACTIVE);
    }

    protected function handleMultipleActiveSubscriptions(OrganisationUnit $rootOu, GetSubscriptionsResponse $response): void
    {
        $this->logWarning(static::LOG_MULTI_SUBSCRIPTION, [$rootOu->getId(), count($response->getSubscriptions())], [static::LOG_CODE, 'Subscription', 'Multi']);
        if (!$this->accountsEmail) {
            return;
        }

        $subscriptionIds = $this->getSubscriptionIdsFromResponse($response);
        $subscriptionIdsText = implode(', ', $subscriptionIds);
        $message = <<<EOS
Hello,

When attempting to send usage statistics to Stripe we found that an OU has more than one active Subscription within Stripe:

* OU ID: {$rootOu->getId()}
* Company: {$rootOu->getAddressCompanyName()}
* Stripe Customer ID: {$rootOu->getStripeId()}
* Stripe Subscription IDs: {$subscriptionIdsText}

Because of this we have NOT been able to send usage stats for this OU.
Please investigate and resolve this issue within Stripe.
EOS;


        mail(
            $this->accountsEmail,
            'Multiple Stripe Subscriptions for OU ' . $rootOu->getId(),
            $message
        );
    }

    protected function getSubscriptionIdsFromResponse(GetSubscriptionsResponse $response): array
    {
        $subscriptionIds = [];
        foreach ($response->getSubscriptions() as $subscription) {
            $subscriptionIds[] = $subscription->getId();
        }
        return $subscriptionIds;
    }

    protected function getOrderSubscriptionItem(Subscription $subscription, OrganisationUnit $rootOu): ?SubscriptionItem
    {
        $item = $subscription->getItemForCgUsageType(Plan::CG_USAGE_TYPE_ORDERS);
        if (!$item) {
            $this->logWarning(static::LOG_NO_ORDER_SI, [$rootOu->getId()], [static::LOG_CODE, 'Subscription', 'NoOrderItem']);
        }
        return $item;
    }

    protected function getOrderCountFromUsage(OrganisationUnit $rootOu, DateTime $usageFrom, DateTime $usageTo): int
    {
        $usage = $this->usageStorage->fetchAggregate(
            $rootOu->getId(),
            'order_count',
            $usageFrom,
            $usageTo
        );

        return $usage ?? 0;
    }

    protected function sendOrderCountToStripe(int $orderCount, SubscriptionItem $subscriptionItem, DateTime $date, OrganisationUnit $rootOu): void
    {
        try {
            $request = $this->buildCreateUsageRequestForSubscriptionItem($subscriptionItem, $orderCount, $date);
            $this->stripeClient->send($request);
            $this->logDebug(static::LOG_USAGE_RECORD_CREATED, [$rootOu->getId()], [static::LOG_CODE, 'UsageRecord', 'Created']);
        } catch (StorageException $e) {
            $this->logAlertException($e, static::LOG_USAGE_RECORD_ERROR, [$rootOu->getId()], [static::LOG_CODE, 'UsageRecord', 'Error']);
        }
    }

    protected function buildCreateUsageRequestForSubscriptionItem(SubscriptionItem $subscriptionItem, int $orderCount, DateTime $date): CreateUsageRecordRequest
    {
        return new CreateUsageRecordRequest($subscriptionItem->getId(), $orderCount, $date->getTimestamp(), CreateUsageRecordRequest::ACTION_INC);
    }
}