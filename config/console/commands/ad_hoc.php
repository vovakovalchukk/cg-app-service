<?php
use CG\Gearman\Client as GearmanClient;
use CG\Order\Client\Gearman\WorkerFunction\SetInvoiceByOU as WorkerFunction;
use CG\Order\Client\Gearman\Workload\SetInvoiceByOU as Workload;
use CG\Stock\Adjustment as StockAdjustment;
use CG\Stock\Command\Adjustment as StockAdjustmentCommand;
use Predis\Client as Redis;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Di\Di;

/** @var Di $di */
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
    ],
    'ad-hoc:correctAllocatedStock' => [
        'description' => 'Correct any discrepancies with allocated stock, by default will display proposed changes and not apply',
        'arguments' => [],
        'options' => [
            'fix' => [
                'description' => 'Apply updates to stock',
            ],
        ],
        'command' =>
            function(InputInterface $input, OutputInterface $output) use ($di) {
                $query = <<<EOF
SELECT s.sku, calc.organisationUnitId, calculatedAllocated as expected, sl.allocated as actual, calculatedAllocated - allocated as diff
FROM stock AS s
INNER JOIN stockLocation AS sl ON s.id = sl.stockId
INNER JOIN (
	SELECT itemSku, item.organisationUnitId, SUM(
		IF(item.purchaseDate > account.cgCreationDate,
			IF(`status` IN ('awaiting payment', 'new','cancelling','dispatching','refunding'), itemQuantity, 0),
			IF(`status` IN ('awaiting payment', 'new'), itemQuantity, 0)
		)) as calculatedAllocated
	FROM item
	INNER JOIN account.account ON item.accountId = account.id
	WHERE item.stockManaged = 1
	GROUP BY itemSku, item.organisationUnitId
) as calc ON calc.itemSku LIKE s.sku AND s.organisationUnitId = calc.organisationUnitId
WHERE calculatedAllocated > allocated
ORDER BY organisationUnitId, sku
EOF;

                /** @var StockAdjustmentCommand $command */
                $command = $di->get(StockAdjustmentCommand::class);
                /** @var Adapter $adapter */
                $adapter = $di->get('cg_appReadSql')->getAdapter();
                /** @var ResultInterface $adjustments */
                $adjustments = $adapter->query($query)->execute();

                $command(
                    $input,
                    $output,
                    StockAdjustment::TYPE_ALLOCATED,
                    iterator_to_array($adjustments),
                    $input->getOption('fix')
                );
            }
    ],
    'ad-hoc:correctOverAllocatedStock' => [
        'description' => 'Correct any discrepancies with allocated stock where we have over allocated, by default will display proposed changes and not apply',
        'arguments' => [],
        'options' => [
            'fix' => [
                'description' => 'Apply updates to stock',
            ],
        ],
        'command' =>
            function(InputInterface $input, OutputInterface $output) use ($di) {
                $query = <<<EOF
SELECT s.sku, calc.organisationUnitId, calculatedAllocated as expected, sl.allocated as actual, calculatedAllocated - allocated as diff, unknownOrders
FROM stock AS s
INNER JOIN stockLocation AS sl ON s.id = sl.stockId
INNER JOIN (
	SELECT itemSku, item.organisationUnitId, SUM(
		IF(item.purchaseDate > account.cgCreationDate,
			IF(`status` IN ('awaiting payment', 'new','cancelling','dispatching','refunding'), itemQuantity, 0),
			IF(`status` IN ('awaiting payment', 'new'), itemQuantity, 0)
		)) as calculatedAllocated,
        SUM(IF(`status` = 'unknown', itemQuantity, 0)) as unknownOrders
	FROM item
	INNER JOIN account.account ON item.accountId = account.id
	WHERE item.stockManaged = 1
	GROUP BY itemSku, item.organisationUnitId
) as calc ON calc.itemSku LIKE s.sku AND s.organisationUnitId = calc.organisationUnitId
WHERE calculatedAllocated < allocated
ORDER BY organisationUnitId, sku
EOF;

                /** @var StockAdjustmentCommand $command */
                $command = $di->get(StockAdjustmentCommand::class);
                /** @var Adapter $adapter */
                $adapter = $di->get('cg_appReadSql')->getAdapter();
                /** @var ResultInterface $adjustments */
                $adjustments = $adapter->query($query)->execute();

                $command(
                    $input,
                    $output,
                    [StockAdjustment::TYPE_ALLOCATED, StockAdjustment::TYPE_ONHAND],
                    iterator_to_array($adjustments),
                    $input->getOption('fix'),
                    ['Unknown Orders']
                );
            }
    ],
    'ad-hoc:validateOrderItemStatus' => [
        'description' => 'Reports any new order items that do not match their orders status since the command last run',
        'arguments' => [],
        'options' => [],
        'command' =>
            function(InputInterface $input, OutputInterface $output) use ($di) {
                $query = <<<EOF
SELECT o.`id` as `orderId`, i.`id` as `orderItemId`, o.`channel`, o.`status` as `orderStatus`, i.`status` as `itemStatus`
FROM `order` o
JOIN item i ON o.id = i.`orderId`
WHERE o.`status` != i.`status`
ORDER BY o.`id`, i.`id`
EOF;

                /** @var Redis $redis */
                $redis = $di->get('reliable_redis');
                /** @var Adapter $cgApp */
                $cgApp = $di->get('cg_appReadSql')->getAdapter();
                /** @var ResultInterface $orderItems */
                $orderItems = $cgApp->query($query)->execute();

                $now = time();
                $lastRun = (int) $redis->getset('ValidateOrderItemStatus:LastRun', (string) $now);

                $count = 0;
                $table = (new Table($output))
                    ->setHeaders(['OrderId', 'OrderItemId', 'Channel', 'OrderStatus', 'OrderItemStatus']);

                foreach ($orderItems as $orderItem) {
                    if (!$redis->sadd('ValidateOrderItemStatus:OrderItemId', $orderItem['orderItemId'])) {
                        continue;
                    }

                    $count++;
                    $table->addRow($orderItem);
                }

                if ($count == 0) {
                    return;
                }

                $output->writeln('The following order items have a different status to their order:');
                $output->writeln('');
                $table->render();

                if ($lastRun) {
                    $output->writeln('');
                    $output->writeln(date('d/m/Y H:i:s', $lastRun) . ' - ' . date('d/m/Y H:i:s', $now));
                }
            }
    ],
];
