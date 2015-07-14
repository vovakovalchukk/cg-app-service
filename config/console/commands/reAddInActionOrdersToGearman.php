<?php

use CG\Account\Client\Service as AccountService;
use CG\Account\Client\Filter as AccountFilter;

use CG\Channel\Gearman\Workload\Order\Dispatch as DispatchWorkload;
use CG\Channel\Gearman\Generator\Order\Dispatch as DispatchGenerator;
use CG\Channel\Gearman\Generator\Order\Cancel as CancelGenerator;

use CG\Order\Service\Service as OrderService;
use CG\Order\Service\Item\Service as OrderItemService;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Shared\Status as OrderStatus;
use CG\Order\Shared\Mapper as OrderMapper;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use CG\Order\Shared\Cancel\Value as CancelValue;
use CG\Order\Shared\Cancel\Item as CancelItem;
use CG\Stdlib\DateTime as CGDateTime;

use CG\Stdlib\Exception\Runtime\NotFound;

return [
    'reAddInActionOrdersToGearman' => [
        'description' => 'Add in progress Orders to gearman queues',
        'arguments' => [],
        'options' => [],
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {

            $accountService = $di->get(AccountService::class);
            $dispatchGenerator = $di->get(DispatchGenerator::class);
            $cancelGenerator = $di->get(CancelGenerator::class);
            $orderMapper = $di->get(OrderMapper::class);
            $orderService = $di->get(OrderService::class);
            $orderItemService = $di->get(OrderItemService::class);

            $filter = new OrderFilter;
            $filter->setLimit('all');
            $filter->setStatus([
                OrderStatus::DISPATCHING,
                OrderStatus::CANCELLING,
                OrderStatus::REFUNDING
            ]);

            $orderCollection = $orderService->fetchCollectionByFilter($filter);

            $accountIdArray = [];
            $orderArray = [];

            foreach ($orderCollection->getRawData() as $orderData) {
                $order = $orderMapper->fromArray($orderData);
                $accountIdArray[] = $order->getAccountId();
                $orderArray[] = $order;
            }

            $accountFilter = new AccountFilter;
            $accountFilter->setId($accountIdArray)
                ->setLimit('all');

            $accountCollection = $accountService->fetchByFilter($accountFilter);

            foreach ($orderArray as $order) {
                $account = $accountCollection->getById($order->getAccountId());
                if (!$account) {
                    $output->writeLn('No account found for <info>' . $order->getAccountId() . '</info>');
                    continue;
                }
                if ($order->getStatus() == OrderStatus::DISPATCHING) {
                    $dispatchGenerator->generateJob($account, $order);
                } elseif ($order->getStatus() == OrderStatus::CANCELLING || $order->getStatus() == OrderStatus::REFUNDING) {
                    $items = [];
                    try {
                        $orderItems = $orderItemService->fetchCollectionByOrderIds([$order->getId()]);
                    } catch (NotFound $e) {
                        continue;
                    }
                    foreach ($orderItems as $item) {
                        $items[] = new CancelItem($item->getId(), $item->getItemQuantity(), $item->getIndividualItemPrice(), 0.00, $item->getItemSku());
                    }
                    $cancelValue = new CancelValue(OrderStatus::REFUNDING ? CancelValue::REFUND_TYPE : CancelValue::CANCEL_TYPE, date(CGDateTime::FORMAT), "Customer no longer wants item", $items, $order->getShippingPrice());
                    $cancelGenerator->generateJob($account, $order, $cancelValue);
                }
                $output->writeLn('Created job for ' . $order->getStatus() . ' <info>' . $order->getId() . '</info>');
            }
        }
    ]
];
