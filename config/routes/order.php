<?php

use Slim\Slim;
use CG\Slim\Versioning\Version;

use CG\Controllers\Order\Order\Collection as OrderCollection;
use CG\InputValidation\Order\Order\Filter as OrderFilterValidationRules;
use CG\InputValidation\Order\Order\Entity as OrderEntityValidationRules;
use CG\Controllers\Order\Note\Collection as NoteCollection;
use CG\Controllers\Order\Order;
use CG\Order\Shared\Entity as OrderEntity;
use CG\Order\Shared\Mapper as OrderMapper;
use CG\Order\Service\Service as OrderService;

//Alert
use CG\Controllers\Order\Alert;
use CG\Controllers\Order\Alert\Collection as AlertCollection;
use CG\InputValidation\Order\Alert\Entity as AlertEntityValidationRules;
use CG\InputValidation\Order\Alert\Filter as AlertFilterValidationRules;
use CG\Order\Shared\Alert\Entity as AlertEntity;
use CG\Order\Shared\Alert\Mapper as AlertMapper;
use CG\Order\Service\Alert\Service as AlertService;

//Archive
use CG\Controllers\Order\Archive;
use CG\InputValidation\Order\Archive\Entity as ArchiveEntityValidationRules;

//Item
use CG\Controllers\Order\Item;
use CG\Controllers\Order\Item\Collection as ItemCollection;
use CG\Controllers\Order\Item\Images as ItemImages;
use CG\InputValidation\Order\Item\Entity as ItemEntityValidationRules;
use CG\InputValidation\Order\Item\Filter as ItemFilterValidationRules;
use CG\InputValidation\Order\Item\Images as ItemImagesValidationRules;
use CG\Order\Shared\Item\Entity as ItemEntity;
use CG\Order\Shared\Item\Mapper as ItemMapper;
use CG\Order\Service\Item\Service as ItemService;
use CG\Order\Service\Item\Image\EtagHelper as ItemImageEtagHelper;

//Fee
use CG\Controllers\Order\Item\Fee;
use CG\Controllers\Order\Item\Fee\Collection as FeeCollection;
use CG\InputValidation\Order\Item\Fee\Entity as FeeEntityValidationRules;
use CG\Slim\InputValidation\PageLimit as FeeFilterValidationRules;
use CG\Order\Shared\Item\Fee\Entity as FeeEntity;
use CG\Order\Shared\Item\Fee\Mapper as FeeMapper;
use CG\Order\Service\Item\Fee\Service as FeeService;

//Note
use CG\Controllers\Order\Note;
use CG\InputValidation\Order\Note\Entity as NoteEntityValidationRules;
use CG\InputValidation\Order\Note\Filter as NoteFilterValidationRules;
use CG\Order\Shared\Note\Entity as NoteEntity;
use CG\Order\Shared\Note\Mapper as NoteMapper;
use CG\Order\Service\Note\Service as NoteService;

//GiftWrap
use CG\Controllers\Order\Item\GiftWrap;
use CG\Controllers\Order\Item\GiftWrap\Collection as GiftWrapCollection;
use CG\InputValidation\Order\Item\GiftWrap\Entity as GiftWrapEntityValidationRules;
use CG\Slim\InputValidation\PageLimit as GiftWrapFilterValidationRules;
use CG\Order\Shared\Item\GiftWrap\Entity as GiftWrapEntity;
use CG\Order\Shared\Item\GiftWrap\Mapper as GiftWrapMapper;
use CG\Order\Service\Item\GiftWrap\Service as GiftWrapService;

//UserChange
use CG\Controllers\Order\UserChange;
use CG\InputValidation\Order\UserChange\Entity as UserChangeEntityValidationRules;
use CG\Order\Shared\UserChange\Entity as UserChangeEntity;
use CG\Order\Shared\UserChange\Mapper as UserChangeMapper;
use CG\Order\Service\UserChange\Service as UserChangeService;

//Batch
use CG\Controllers\Order\Batch;
use CG\Controllers\Order\Batch\Collection as BatchCollection;
use CG\InputValidation\Order\Batch\Entity as BatchEntityValidationRules;
use CG\InputValidation\Order\Batch\Filter as BatchFilterValidationRules;
use CG\Order\Shared\Batch\Entity as BatchEntity;
use CG\Order\Shared\Batch\Mapper as BatchMapper;
use CG\Order\Service\Batch\Service as BatchService;

//Tracking
use CG\Controllers\Order\Tracking as OrderTracking;
use CG\Controllers\Order\Tracking\Collection as OrderTrackingCollection;
use CG\InputValidation\Order\Tracking\Entity as OrderTrackingEntityValidationRules;
use CG\InputValidation\Order\Tracking\Filter as OrderTrackingFilterValidationRules;
use CG\Order\Shared\Tracking\Entity as TrackingEntity;
use CG\Order\Shared\Tracking\Mapper as TrackingMapper;
use CG\Order\Service\Tracking\Service as TrackingService;

return [
    '/order' => [
        'controllers' => function () use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();
            $controller = $di->get(OrderCollection::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'PATCH', 'OPTIONS'],
        'name' => 'OrderCollection',
        'validation' => ['dataRules' => null, 'filterRules' => OrderFilterValidationRules::class, 'flatten' => false],
        'version' => new Version(1, 21),
        'entityRoute' => '/order/:orderId'
    ],
    '/order/:orderId' => [
        'controllers' => function ($orderId) use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(Order::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($orderId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'name' => 'OrderEntity',
        'validation' => ['dataRules' => OrderEntityValidationRules::class, 'filterRules' => null, 'flatten' => false],
        'version' => new Version(1, 21),
        'eTag' => [
            'mapperClass' => OrderMapper::class,
            'entityClass' => OrderEntity::class,
            'serviceClass' => OrderService::class
        ],
    ],
    '/order/:orderId/note' => [
        'controllers' => function ($orderId) use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(NoteCollection::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($orderId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'OrderNoteCollection',
        'entityRoute' => '/order/:orderId/note/:noteId',
        'validation' => [
            'dataRules' => NoteEntityValidationRules::class,
            'filterRules' => NoteFilterValidationRules::class,
            'flatten' => false
        ]
    ],
    '/order/:orderId/note/:noteId' => [
        'controllers' => function ($orderId, $noteId) use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(Note::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($orderId, $noteId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'OrderNoteEntity',
        'validation' => ['dataRules' => NoteEntityValidationRules::class, 'filterRules' => null, 'flatten' => false],
        'eTag' => [
            'mapperClass' => NoteMapper::class,
            'entityClass' => NoteEntity::class,
            'serviceClass' => NoteService::class
        ]
    ],
    '/order/:orderId/tracking' => [
        'controllers' => function ($orderId) use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(OrderTrackingCollection::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($orderId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'entityRoute' => '/order/:orderId/tracking/:trackingId',
        'name' => 'OrderTrackingCollection',
        'validation' => [
            'dataRules' => OrderTrackingEntityValidationRules::class,
            'filterRules' => OrderTrackingFilterValidationRules::class,
            'flatten' => false
        ],
        'version' => new Version(1, 4),
    ],
    '/order/:orderId/tracking/:trackingId' => [
        'controllers' => function ($orderId, $trackingId) use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(OrderTracking::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($orderId, $trackingId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'OrderTrackingEntity',
        'validation' => [
            'dataRules' => OrderTrackingEntityValidationRules::class,
            'filterRules' => null,
            'flatten' => false
        ],
        'version' => new Version(1, 4),
        'eTag' => [
            'mapperClass' => TrackingMapper::class,
            'entityClass' => TrackingEntity::class,
            'serviceClass' => TrackingService::class
        ]
    ],
    '/order/:orderId/alert' => [
        'controllers' => function ($orderId) use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(AlertCollection::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($orderId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'entityRoute' => '/order/:orderId/alert/:alertId',
        'name' => 'OrderAlertCollection',
        'validation' => [
            'dataRules' => AlertEntityValidationRules::class,
            'filterRules' => AlertFilterValidationRules::Class,
            'flatten' => false
        ]
    ],
    '/order/:orderId/alert/:alertId' => [
        'controllers' => function ($orderId, $alertId) use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(Alert::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($orderId, $alertId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'OrderAlertEntity',
        'validation' => ['dataRules' => AlertEntityValidationRules::class, 'filterRules' => null, 'flatten' => false],
        'eTag' => [
            'mapperClass' => AlertMapper::class,
            'entityClass' => AlertEntity::class,
            'serviceClass' => AlertService::class
        ]
    ],
    '/order/:orderId/archive' => [
        'controllers' => function ($orderId) use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(Archive::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($orderId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'OPTIONS'],
        'name' => 'OrderArchiveEntity',
        'validation' => ['dataRules' => ArchiveEntityValidationRules::class, 'filterRules' => null, 'flatten' => false]
    ],
    '/order/:orderId/userChange' => [
        'controllers' => function ($orderId) use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(UserChange::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($orderId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'OrderUserChangeEntity',
        'validation' => [
            'dataRules' => UserChangeEntityValidationRules::class,
            'filterRules' => null,
            'flatten' => false
        ],
        'eTag' => [
            'mapperClass' => UserChangeMapper::class,
            'entityClass' => UserChangeEntity::class,
            'serviceClass' => UserChangeService::class
        ]
    ],
    '/orderItem' => [
        'controllers' => function () use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();
            $controller = $di->get(ItemCollection::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request->getBody())
            );
        },
        'via' => ['GET', 'PATCH', 'OPTIONS'],
        'name' => 'OrderItemCollection',
        'validation' => ['dataRules' => null, 'filterRules' => ItemFilterValidationRules::class, 'flatten' => false],
        'version' => new Version(1, 13),
        'entityRoute' => '/orderItem/:orderItemId'
    ],
    '/orderItem/:orderItemId' => [
        'controllers' => function ($orderItemId) use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(Item::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($orderItemId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'OPTIONS', 'DELETE', 'PATCH'],
        'name' => 'OrderItemEntity',
        'validation' => ['dataRules' => ItemEntityValidationRules::class, 'filterRules' => null, 'flatten' => false],
        'version' => new Version(1, 13),
        //Updating this requires an update to /orderItem/:orderItemId/images route also (Line 346)
        'eTag' => [
            'mapperClass' => ItemMapper::class,
            'entityClass' => ItemEntity::class,
            'serviceClass' => ItemService::class
        ]
    ],
    '/orderItem/:orderItemId/images' => [
        'controllers' => function ($orderItemId) use ($di) {
            /** @var Slim $app */
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();
            $controller = $di->get(ItemImages::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($orderItemId, $app->request()->getBody())
            );
        },
        'via' => ['PUT', 'OPTIONS'],
        'name' => 'OrderItemImages',
        'validation' => ['dataRules' => ItemImagesValidationRules::class, 'filterRules' => null, 'flatten' => false],
        'version' => new Version(1, 13),
        'eTag' => [
            'mapperClass' => ItemMapper::class,
            'entityClass' => ItemEntity::class,
            'serviceClass' => ItemService::class,
            'helper' => function () use ($di, $app) {
                return $di->get(ItemImageEtagHelper::class);
            }
        ],
    ],
    '/orderItem/:orderItemId/fee' => [
        'controllers' => function ($orderItemId) use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(FeeCollection::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($orderItemId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'OrderItemFeeCollection',
        'entityRoute' => '/orderItem/:orderItemId/fee/:feeId',
        'validation' => [
            'dataRules' => FeeEntityValidationRules::class,
            'filterRules' => FeeFilterValidationRules::class,
            'flatten' => false
        ]
    ],
    '/orderItem/:orderItemId/fee/:feeId' => [
        'controllers' => function ($orderItemId, $feeId) use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(Fee::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($orderItemId, $feeId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'OrderItemFeeEntity',
        'validation' => ['dataRules' => FeeEntityValidationRules::class, 'filterRules' => null, 'flatten' => false],
        'eTag' => [
            'mapperClass' => FeeMapper::class,
            'entityClass' => FeeEntity::class,
            'serviceClass' => FeeService::class
        ]
    ],
    '/orderItem/:orderItemId/giftWrap' => [
        'controllers' => function ($orderItemId) use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(GiftWrapCollection::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($orderItemId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'entityRoute' => '/orderItem/:orderItemId/giftWrap/:giftWrapId',
        'name' => 'OrderItemGiftWrapCollection',
        'version' => new Version(1, 2),
        'validation' => [
            'dataRules' => GiftWrapEntityValidationRules::class,
            'filterRules' => GiftWrapFilterValidationRules::class,
            'flatten' => false
        ]
    ],
    '/orderItem/:orderItemId/giftWrap/:giftWrapId' => [
        'controllers' => function ($orderItemId, $giftWrapId) use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(GiftWrap::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($orderItemId, $giftWrapId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'OrderItemGiftWrapEntity',
        'version' => new Version(1, 2),
        'validation' => [
            'dataRules' => GiftWrapEntityValidationRules::class,
            'filterRules' => null,
            'flatten' => false
        ],
        'eTag' => [
            'mapperClass' => GiftWrapMapper::class,
            'entityClass' => GiftWrapEntity::class,
            'serviceClass' => GiftWrapService::class
        ]
    ],
    '/orderBatch' => [
        'controllers' => function () use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();
            $controller = $di->get(BatchCollection::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method()
            );
        },
        'via' => ['GET', 'OPTIONS'],
        'entityRoute' => '/orderBatch/:batchId',
        'name' => 'OrderBatchCollection',
        'validation' => ['dataRules' => null, 'filterRules' => BatchFilterValidationRules::class, 'flatten' => false]
    ],
    '/orderBatch/:batchId' => [
        'controllers' => function ($batchId) use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(Batch::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($batchId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'OrderBatchEntity',
        'validation' => ['dataRules' => BatchEntityValidationRules::class, 'filterRules' => null, 'flatten' => false],
        'eTag' => [
            'mapperClass' => BatchMapper::class,
            'entityClass' => BatchEntity::class,
            'serviceClass' => BatchService::class
        ]
    ],
];