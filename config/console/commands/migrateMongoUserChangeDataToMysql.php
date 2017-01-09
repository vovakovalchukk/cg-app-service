<?php
use CG\Order\Service\UserChange\Storage\Db as DbStorage;
use CG\Order\Shared\UserChange\Mapper as Mapper;
use CG\Stdlib\Exception\Runtime\NotFound;
use Symfony\Component\Console\Input\InputInterface;

return array(
    'phinx:migrateMongoUserChangeDataToMysql' => array(
        'command' => function (InputInterface $input) use ($di) {

            $mongoClient = $di->get('mongodb');

            $mapper = $di->get(Mapper::class);
            $dbStorage = $di->get(DbStorage::class);

            $result = $mongoClient->order->userChange->find();

            foreach ($result as $userChangeData) {
                $userChange = $mapper->fromArray($userChangeData);
                try {
                    $dbStorage->fetch($userChange->getId());
                    continue;
                } catch (NotFound $ex) {
                    $dbStorage->save($userChange);
                }
            }
        },
        'description' => 'Adds the mongo user-change data to mysql',
        'arguments' => [],
        'options' => []
    )
);
