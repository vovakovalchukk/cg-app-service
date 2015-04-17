<?php

use CG\Account\Client\Service as AccountService;
use CG\Account\Client\Filter as AccountFilter;

use CG\Channel\Gearman\Workload\Order\Dispatch as DispatchWorkload;
use CG\Channel\Gearman\Generator\Order\Dispatch as DispatchGenerator;

use CG\Order\Service\Service as OrderService;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Shared\Status as OrderStatus;
use CG\Order\Shared\Mapper as OrderMapper;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

return [
    'reAddInActionOrdersToGearman' => [
        'description' => 'Add in progress Orders to gearman queues',
        'arguments' => [],
        'options' => [],
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {

            $accountService = $di->get(AccountService::class);
            $dispatchGenerator = $di->get(DispatchGenerator::class);
            $orderMapper = $di->get(OrderMapper::class);
            $orderService = $di->get(OrderService::class);

            $filter = new OrderFilter;
            $filter->setStatus([
                OrderStatus::DISPATCHING,
            ]);

            $orderCollection = $orderService->fetchCollectionByFilter($filter);

            $accountIdArray = [];
            $orderArray = [];

            foreach ($orderCollection->getRawData() as $orderData) {
                $order = $orderMapper->fromArray($orderData);
                $accountIdsArray[] = $order->getAccountId();
                $orderArray[] = $order;
            };

            $accountFilter = new AccountFilter;
            $accountFilter->setId($accountIdArray);

            $accountCollection = $accountService->fetchByFilter($accountFilter);

            foreach ($orderArray as $order) {
                $account = $accountCollection->getById($order->getAccountId());
                $dispatchGenerator->generateJob($account, $order);
                $output->writeLn('Created job for <info>' . $order->getId() . '</info>');
            }
        }
    ]
];
