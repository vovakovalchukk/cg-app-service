<?php
use CG\Order\Service\UserChange\Storage\Db as DbStorage;
use CG\Order\Shared\UserChange\Collection as Collection;
use CG\Order\Shared\UserChange\Mapper as Mapper;
use Symfony\Component\Console\Input\InputInterface;

return array(
    'phinx:migrateMongoUserChangeDataToMysql' => array(
        'command' => function (InputInterface $input) use ($di) {

            $mongoClient = $di->get('mongodb');

            $mapper = $di->get(Mapper::class);
            $collection = new Collection($mapper->getEntityClass(), __FUNCTION__);
            $dbStorage = $di->get(DbStorage::class);

            $result = $mongoClient->order->userChange->find();

            foreach ($result as $userChangeData) {
                $collection->attach($mapper->fromArray($userChangeData));
            }

            $dbStorage->saveCollection($collection);
        },
        'description' => 'Adds the mongo user-change data to mysql',
        'arguments' => [],
        'options' => []
    )
);
