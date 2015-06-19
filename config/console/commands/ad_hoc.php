<?php
use CG\Gearman\Client as GearmanClient;
use CG\Order\Client\Gearman\WorkerFunction\SetInvoiceByOU as WorkerFunction;
use CG\Order\Client\Gearman\Workload\SetInvoiceByOU as Workload;
use CG\OrganisationUnit\Entity as OU;
use CG\OrganisationUnit\Service as OUService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

return [
    'ad-hoc:retrofitInvoiceNumbers' => [
        'description' => 'Retro fit sequential invoice Numbers for existing orders in CG',
        'arguments' => [],
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            /**
             * @var OUService $ouService
             */
            $ouService = $di->get(OUService::class);
            /**
             * @var GearmanClient $gearmanClient
             */
            $gearmanClient = $di->get(GearmanClient::class);

            /**
             * @var OU $ou
             */
            foreach ($ouService->fetchFiltered('all', 1) as $ou) {
                $workload = new Workload($ou->getId());
                $gearmanClient->doBackground(
                    WorkerFunction::FUNCTION_NAME,
                    serialize($workload),
                    WorkerFunction::FUNCTION_NAME . '-' . $ou->getId()
                );
            }
        },
    ]
];
