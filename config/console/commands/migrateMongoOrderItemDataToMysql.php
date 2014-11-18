<?php
use CG\Order\Service\Storage\Persistent as OrderStorage;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Mapper as OrderMapper;
use Symfony\Component\Console\Input\InputInterface;

return array(
    'phinx:migrateMongoOrderItemDataToMysql' => array(
        'command' => function (InputInterface $input) use ($di) {

            echo 'order item';

            return;

            $mongoClient = $di->get('mongodb');

            $orderMapper = $di->get(OrderMapper::class);
            $orderCollection = new OrderCollection($orderMapper->getEntityClass(), __FUNCTION__);
            $orderStorage = $di->get(OrderStorage::class);

            $aggregate = [];
            $query = [];
            $query['archived'] = true;
            $aggregate['match'] = ['$match' => $query];

            $response = $mongoClient->order->order->aggregate(array_values($aggregate));
            $orders = $response['result'];

            printf("Archived orders: %d\n", count($orders));

            foreach($orders as $order) {
                $order['id'] = $order['_id'];
                $orderCollection->attach($orderMapper->fromArray($order));
            }

            $orderStorage->saveCollection($orderCollection);
        },
        'description' => 'Adds the mongo order data to mysql',
        'arguments' => [],
        'options' => []
    )
);
