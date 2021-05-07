<?php
namespace CG\Order\Command;

use CG\Account\AccountLockingStampedePrevention;
use CG\Account\Shared\Collection as Accounts;
use CG\Account\Shared\Entity as Account;
use CG\Channel\Gearman\Generator\Order\Dispatch as DispatchGenerator;
use CG\Channel\Gearman\Generator\Order\Cancel as CancelGenerator;
use CG\Cilex\ModulusAwareInterface;
use CG\Cilex\ModulusTrait;
use CG\Order\Service\Service as OrderService;
use CG\Order\Service\Cancel\Service as OrderCancelService;
use CG\Order\Service\Item\Service as OrderItemService;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Shared\Cancel\Collection as Cancellations;
use CG\Order\Shared\Collection as Orders;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Status as OrderStatus;
use CG\Order\Shared\Mapper as OrderMapper;
use CG\Order\Shared\Cancel\Value as CancelValue;
use CG\Order\Shared\Cancel\Item as CancelItem;
use CG\Stdlib\DateTime as CGDateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class ReAddInActionOrdersToGearman implements LoggerAwareInterface, ModulusAwareInterface
{
    use LogTrait;
    use ModulusTrait;

    public const LOG_CODE = 'ReAddInActionOrdersToGearman';
    protected const CANCEL_TYPE_TO_STATUS = [
        CancelValue::REFUND_TYPE => OrderStatus::REFUNDING,
        CancelValue::CANCEL_TYPE => OrderStatus::CANCELLING,
        CancelValue::PARTIAL_REFUND_TYPE => OrderStatus::REFUNDING,
    ];

    protected $dispatchGenerator;
    protected $cancelGenerator;
    protected $orderMapper;
    protected $orderService;
    protected $orderItemService;
    protected $accountLockingStampedePrevention;
    protected $orderCancelService;

    public function __construct(
        DispatchGenerator $dispatchGenerator,
        CancelGenerator $cancelGenerator,
        OrderMapper $orderMapper,
        OrderService $orderService,
        OrderItemService $orderItemService,
        AccountLockingStampedePrevention $accountLockingStampedePrevention,
        OrderCancelService $orderCancelService
    ) {
        $this->dispatchGenerator = $dispatchGenerator;
        $this->cancelGenerator = $cancelGenerator;
        $this->orderMapper = $orderMapper;
        $this->orderService = $orderService;
        $this->orderItemService = $orderItemService;
        $this->accountLockingStampedePrevention = $accountLockingStampedePrevention;
        $this->orderCancelService = $orderCancelService;
    }

    public function __invoke()
    {
        $accounts = $this->accountLockingStampedePrevention->retrieveAccounts();
        $this->filterCollection($accounts);

        try {
            $orders = $this->fetchOrders($accounts);
        } catch (NotFound $e) {
            return;
        }
        $orderArray = [];
        foreach ($orders->getRawData() as $orderData) {
            $order = $this->orderMapper->fromArray($orderData);
            $orderArray[$order->getId()] = $order;
        }
        $this->logDebug('Found %s orders in actionable status', [count($orderArray)], static::LOG_CODE);
        $cancellations = $this->fetchCancellations(array_keys($orderArray));
        /** @var Order $order */
        foreach ($orderArray as $order) {
            $this->addGlobalLogEventParams(['order' => $order->getId(), 'account' => $order->getAccountId(), 'rootOu' => $order->getRootOrganisationUnitId()]);
            $account = $accounts->getById($order->getAccountId());
            $orderCancellations = $cancellations[$order->getId()] ?? null;
            if (!$account) {
                $this->logDebug('No account found with id %s found, skipping order %s', [$order->getAccountId(), $order->getId()], static::LOG_CODE);
                continue;
            }
            $this->generateJobForOrder($order, $account, $orderCancellations);
            $this->removeGlobalLogEventParams(['order', 'account', 'rootOu']);
        }
    }

    protected function fetchOrders(Accounts $accounts): Orders
    {
        $filter = new OrderFilter;
        $filter
            ->setLimit('all')
            ->setArchived(false)
            ->setAccountId(array_values($accounts->getIds()));
        $filter->setStatus([
            OrderStatus::DISPATCHING,
            OrderStatus::CANCELLING,
            OrderStatus::REFUNDING
        ]);

        return $this->orderService->fetchCollectionByFilter($filter);
    }

    protected function generateJobForOrder(Order $order, Account $account, ?Cancellations $orderCancellations)
    {
        if ($order->getStatus() == OrderStatus::DISPATCHING) {
            $this->requeueDispatch($order, $account);
        } elseif ($order->getStatus() == OrderStatus::CANCELLING || $order->getStatus() == OrderStatus::REFUNDING) {
            try {
                $cancelValue = $this->fetchCancelValue($order, $orderCancellations);
                $this->cancelGenerator->generateJob($account, $order, $cancelValue);
                $this->logDebug('Created %s job for order %s', [$cancelValue->getType(), $order->getId()], static::LOG_CODE);
            } catch (NotFound $e) {
                return;
            }
        }
    }

    protected function requeueDispatch(Order $order, Account $account): void
    {
        $this->dispatchGenerator->generateJob($account, $order);
        $this->logDebug('Created dispatch job for order %s', [$order->getId()], static::LOG_CODE);
    }

    /**
     * @return Cancellations[]
     */
    protected function fetchCancellations(array $orderIds): array
    {
        return $this->orderCancelService->fetchByOrderIds($orderIds);
    }

    protected function fetchCancelValue(Order $order, ?Cancellations $orderCancellations): CancelValue
    {
        try {
            return $this->fetchMatchingCancelValue($order, $orderCancellations);
        } catch (NotFound $e) {
            return $this->reconstructCancelValue($order);
        } catch (\Throwable $e) {
            throw new NotFound('Could not find or reconstruct cancel value for order ' . $order->getId());
        }
    }

    protected function fetchMatchingCancelValue(Order $order, ?Cancellations $orderCancellations): CancelValue
    {
        if ($orderCancellations === null) {
            throw new NotFound();
        }
        /** @var CancelValue $cancelValue */
        foreach ($orderCancellations as $cancelValue) {
            if ($order->getStatus() == static::CANCEL_TYPE_TO_STATUS[$cancelValue->getType()]) {
                return $cancelValue;
            }
        }
        throw new NotFound();
    }

    protected function reconstructCancelValue(Order $order): CancelValue
    {
        try {
            $orderItems = $this->orderItemService->fetchCollectionByOrderIds([$order->getId()]);
        } catch (NotFound $e) {
            $this->logDebug('Skipping creating cancellation/refund job for order %s as no order items were found', [$order->getId()], static::LOG_CODE);
            throw $e;
        }
        foreach ($orderItems as $item) {
            $items[] = new CancelItem($item->getId(), $item->getItemQuantity(), $item->getIndividualItemPrice(), 0.00, $item->getItemSku());
        }
        return new CancelValue($order->getStatus() == OrderStatus::REFUNDING ? CancelValue::REFUND_TYPE : CancelValue::CANCEL_TYPE, date(CGDateTime::FORMAT), "Customer no longer wants item", $items, $order->getShippingPrice());
    }
}