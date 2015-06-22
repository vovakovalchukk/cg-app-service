<?php
use CG\Gearman\Client as GearmanClient;
use CG\Order\Client\Gearman\WorkerFunction\SetInvoiceByOU as WorkerFunction;
use CG\Order\Client\Gearman\Workload\SetInvoiceByOU as Workload;
use CG\OrganisationUnit\Entity as OU;
use CG\OrganisationUnit\Service as OUService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CG\Zend\Stdlib\Db\Sql\Sql as SqlClient;

return [
    'ad-hoc:retrofitInvoiceNumbers' => [
        'description' => 'Retro fit sequential invoice Numbers for existing orders in CG',
        'arguments' => [],
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            /**
             * @var GearmanClient $gearmanClient
             */
            $gearmanClient = $di->get(GearmanClient::class);

            $sqlClient = $di->get('ReadCGSql');
            $query = 'SELECT organisationUnitId, COUNT(*) AS orderCount
                FROM `order`
                GROUP BY organisationUnitId
                ORDER BY orderCount DESC';
            $results = $sqlClient->getAdapter()->query($query)->execute();
            foreach ($results as $row) {
                $ouId = $row['organisationUnitId'];
                $workload = new Workload($ouId);
                $gearmanClient->doBackground(
                    WorkerFunction::FUNCTION_NAME,
                    serialize($workload),
                    WorkerFunction::FUNCTION_NAME . '-' . $ouId
                );
            }
        },
    ]
];
