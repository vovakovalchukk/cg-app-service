<?php
use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use CG\Order\Client\Gearman\WorkerFunction\SetInvoiceId as SetInvoiceIdWorkerFunction;
use CG\Order\Client\Gearman\Workload\SetInvoiceId as SetInvoiceIdWorkload;
use CG\Order\Service\Service as OrderService;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Shared\Mapper as OrderMapper;
use CG\Order\Shared\Status as OrderStatus;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\NotFound;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

return [
    'ad-hoc:retrofitInvoiceNumbers' => [
        'description' => 'Retro fit sequential invoice Numbers for existing orders in CG',
        'arguments' => [],
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {

            $gearmanClient = $di->get(GearmanClient::class);
            $gearmanClient->addServer('192.168.33.51', 4730); // TODO shouldn't have to do this...

            $accountService = $di->get(AccountService::class);
            $orderMapper = $di->get(OrderMapper::class);
            $orderService = $di->get(OrderService::class);
            $organisationUnitService = $di->get(OrganisationUnitService::class);

            $accountFilter = new AccountFilter;
            $accountFilter
                ->setLimit('all')
                ->setDeleted(false);

            $accountCollection = $accountService->fetchByFilter($accountFilter);
            $ouCollection = $organisationUnitService->fetchRootOus('all', 1);

            $orderStatuses = array_diff(
                OrderStatus::getAllStatuses(),
                [OrderStatus::CANCELLED]
            );

            $orderFilter = new OrderFilter;
            $orderFilter
                ->setLimit('all')
                ->setPage(1)
                ->setOrderBy('purchaseDate')
                ->setOrderDirection('ASC')
                ->setStatus($orderStatuses);

            foreach ($ouCollection as $ou) {
                $orderFilter->setOrganisationUnitId([$ou->getId()]);
                try {
                    $orderCollection = $orderService->fetchCollectionByFilter($orderFilter);
                    $output->writeln(sprintf('OU: %d; Orders: %d', $ou->getId(), count($orderCollection->getRawData())));

                    foreach ($orderCollection->getRawData() as $orderRawData) {
                        try {
                            $account = $accountCollection->getById($orderRawData['accountId']);
                        } catch (NotFound $e) {
                            continue;
                        }
                        if (strtotime($account->getCgCreationDate()) > strtotime($orderRawData['purchaseDate'])) {
                            continue;
                        }
                        if ($orderRawData['invoiceNumber'] != null) {
                            continue;
                        }

                        $order = $orderMapper->fromArray($orderRawData);

                        $workload = new SetInvoiceIdWorkload($order);
                        $gearmanClient->doBackground(
                            SetInvoiceIdWorkerFunction::FUNCTION_NAME,
                            serialize($workload),
                            SetInvoiceIdWorkerFunction::FUNCTION_NAME . '-' . $order->getId()
                        );
                    }
                } catch (NotFound $e) {
                    $output->writeln(sprintf('OU: %d; Orders: 0', $ou->getId()));
                    // no orders exist for OU, continue...
                }

            }
        },
    ]
];