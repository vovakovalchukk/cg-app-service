<?php
namespace CG\Order\Command;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Filter as AccountFilter;
use CG\Channel\Gearman\Generator\Order\Dispatch as DispatchGenerator;
use CG\Channel\Gearman\Generator\Order\Cancel as CancelGenerator;
use CG\Order\Service\Service as OrderService;
use CG\Order\Service\Item\Service as OrderItemService;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Shared\Status as OrderStatus;
use CG\Order\Shared\Mapper as OrderMapper;
use CG\Order\Shared\Cancel\Value as CancelValue;
use CG\Order\Shared\Cancel\Item as CancelItem;
use CG\Stdlib\DateTime as CGDateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class ReAddInActionOrdersToGearman implements LoggerAwareInterface
{
    use LogTrait;

    protected $accountService;
    protected $dispatchGenerator;
    protected $cancelGenerator;
    protected $orderMapper;
    protected $orderService;
    protected $orderItemService;

    const LOG_CODE = 'ReAddInActionOrdersToGearman';

    public function __construct(
        AccountService $accountService,
        DispatchGenerator $dispatchGenerator,
        CancelGenerator $cancelGenerator,
        OrderMapper $orderMapper,
        OrderService $orderService,
        OrderItemService $orderItemService
    ) {
        $this->accountService = $accountService;
        $this->dispatchGenerator = $dispatchGenerator;
        $this->cancelGenerator = $cancelGenerator;
        $this->orderMapper = $orderMapper;
        $this->orderService = $orderService;
        $this->orderItemService = $orderItemService;
    }

    public function __invoke()
    {
        $orders = $this->fetchOrders();

        $accountIdArray = [];
        $orderArray = [];

        foreach ($orders->getRawData() as $orderData) {
            $order = $this->orderMapper->fromArray($orderData);
            $accountIdArray[] = $order->getAccountId();
            $orderArray[] = $order;
        }

        $this->logDebug('Found %s orders in actionable status', [$orders->count()], static::LOG_CODE);

        $accounts = $this->fetchAccounts($accountIdArray);

        foreach ($orderArray as $order) {
            $this->addGlobalLogEventParams(['order' => $order->getId(), 'account' => $order->getAccountId(), 'rootOu' => $order->getRootOrganisationUnitId()]);
            $account = $accounts->getById($order->getAccountId());
            if (!$account) {
                $this->logDebug('No account found with id %s found, skipping order %s', [$order->getAccountId(), $order->getId()], static::LOG_CODE);
                continue;
            }
            $this->generateJobForOrder($order, $account);
            $this->removeGlobalLogEventParams(['order', 'account', 'rootOu']);
        }
    }

    protected function fetchOrders()
    {
        $filter = new OrderFilter;
        $filter->setLimit('all');
        $filter->setStatus([
            OrderStatus::DISPATCHING,
            OrderStatus::CANCELLING,
            OrderStatus::REFUNDING
        ]);

        return $this->orderService->fetchCollectionByFilter($filter);
    }

    protected function fetchAccounts($accountIds)
    {
        $accountIdsUnique = array_keys(array_flip($accountIds));
        $accountFilter = new AccountFilter;
        $accountFilter
            ->setId($accountIdsUnique)
            ->setActive(true)
            ->setDeleted(false)
            ->setPending(false)
            ->setLimit('all');

        return $this->accountService->fetchByFilter($accountFilter, true);
    }

    protected function generateJobForOrder($order, $account)
    {
        if ($order->getStatus() == OrderStatus::DISPATCHING) {
            $this->dispatchGenerator->generateJob($account, $order);
            $this->logDebug('Created dispatch job for order %s', [$order->getId()], static::LOG_CODE);
        } elseif ($order->getStatus() == OrderStatus::CANCELLING || $order->getStatus() == OrderStatus::REFUNDING) {
            $items = [];
            try {
                $orderItems = $this->orderItemService->fetchCollectionByOrderIds([$order->getId()]);
            } catch (NotFound $e) {
                $this->logDebug('Skipping creating cancellation/refund job for order %s as no order items were found', [$order->getId()], static::LOG_CODE);
                return;
            }
            foreach ($orderItems as $item) {
                $items[] = new CancelItem($item->getId(), $item->getItemQuantity(), $item->getIndividualItemPrice(), 0.00, $item->getItemSku());
            }
            $cancelValue = new CancelValue($order->getStatus() == OrderStatus::REFUNDING ? CancelValue::REFUND_TYPE : CancelValue::CANCEL_TYPE, date(CGDateTime::FORMAT), "Customer no longer wants item", $items, $order->getShippingPrice());
            $this->cancelGenerator->generateJob($account, $order, $cancelValue);
            $this->logDebug('Created %s job for order %s', [$cancelValue->getType(), $order->getId()], static::LOG_CODE);
        }
    }
}