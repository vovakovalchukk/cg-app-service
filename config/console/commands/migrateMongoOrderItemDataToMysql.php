<?php
use CG\Order\Service\Item\Storage\Persistent as ItemStorage;
use CG\Order\Shared\Item\Collection as ItemCollection;
use CG\Order\Shared\Item\Mapper as ItemMapper;
use Symfony\Component\Console\Input\InputInterface;

return array(
    'phinx:migrateMongoOrderItemDataToMysql' => array(
        'command' => function (InputInterface $input) use ($di) {
            $mongoClient = $di->get('mongodb');
            $itemMapper = $di->get(ItemMapper::class);
            $itemCollection = new ItemCollection($itemMapper->getEntityClass(), __FUNCTION__);
            $itemStorage = $di->get(ItemStorage::class);

            $response = $mongoClient->order->item->aggregate([]);
            $items = $response['result'];

            printf("Items: %d\n", count($items));

            $chunksOfItems = array_chunk($items, 1000);

            foreach ($chunksOfItems as $itemChunk) {

                $collection = clone $itemCollection;

                foreach ($itemChunk as $item) {
                    $item['id'] = $item['_id'];
                    $collection->attach($itemMapper->fromArray($item));
                }
                $itemStorage->saveCollection($collection);
            }
        },
        'description' => 'Adds the mongo order data to mysql',
        'arguments' => [],
        'options' => []
    )
);
