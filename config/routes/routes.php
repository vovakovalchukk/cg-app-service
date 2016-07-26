<?php
use Slim\Slim;
use CG\Slim\Versioning\Version;

use CG\Controllers\Root;
use CG\Controllers\Order\Order\Collection as OrderCollection;
use CG\InputValidation\Order\Order\Filter as OrderFilterValidationRules;
use CG\InputValidation\Order\Order\Entity as OrderEntityValidationRules;
use CG\Controllers\Order\Note\Collection as NoteCollection;
use CG\Controllers\Order\Order;
use CG\Order\Shared\Entity as OrderEntity;
use CG\Order\Shared\Mapper as OrderMapper;
use CG\Order\Service\Service as OrderService;

//Tracking
use CG\Controllers\Order\Tracking as OrderTracking;
use CG\Controllers\Order\Tracking\Collection as OrderTrackingCollection;
use CG\InputValidation\Order\Tracking\Entity as OrderTrackingEntityValidationRules;
use CG\InputValidation\Order\Tracking\Filter as OrderTrackingFilterValidationRules;
use CG\Order\Shared\Tracking\Entity as TrackingEntity;
use CG\Order\Shared\Tracking\Mapper as TrackingMapper;
use CG\Order\Service\Tracking\Service as TrackingService;
use CG\Controllers\Tracking\Collection as TrackingCollection;
use CG\InputValidation\Tracking\Filter as TrackingFilterValidationRules;

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
use CG\InputValidation\Order\Item\Entity as ItemEntityValidationRules;
use CG\InputValidation\Order\Item\Filter as ItemFilterValidationRules;
use CG\Order\Shared\Item\Entity as ItemEntity;
use CG\Order\Shared\Item\Mapper as ItemMapper;
use CG\Order\Service\Item\Service as ItemService;

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

//UserPreference
use CG\Controllers\UserPreference\UserPreference;
use CG\Controllers\UserPreference\UserPreference\Collection as UserPreferenceCollection;
use CG\InputValidation\UserPreference\Entity as UserPreferenceEntityValidationRules;
use CG\Slim\InputValidation\PageLimit as UserPreferenceFilterValidationRules;

//Tag
use CG\Controllers\Order\Tag;
use CG\Controllers\Order\Tag\Collection as TagCollection;
use CG\InputValidation\Order\Tag\Entity as TagEntityValidationRules;
use CG\InputValidation\Order\Tag\Filter as TagFilterValidationRules;
use CG\Order\Shared\Tag\Entity as TagEntity;
use CG\Order\Shared\Tag\Mapper as TagMapper;
use CG\Order\Service\Tag\Service as TagService;

//Filter
use CG\Controllers\Order\Filter;
use CG\Controllers\Order\Filter\Collection as FilterCollection;

// Label
use CG\Controllers\Order\Label as LabelController;
use CG\Controllers\Order\Label\Collection as LabelCollectionController;
use CG\InputValidation\Order\Label\Entity as LabelEntityValidationRules;
use CG\InputValidation\Order\Label\Filter as LabelFilterValidationRules;
use CG\Order\Shared\Label\Entity as LabelEntity;
use CG\Order\Shared\Label\Mapper as LabelMapper;
use CG\Order\Service\Label\Service as LabelService;

//ShippingMethod
use CG\Controllers\Shipping\Method\Method as ShippingMethod;
use CG\Controllers\Shipping\Method\Method\Collection as ShippingMethodCollection;
use CG\InputValidation\Shipping\Method\Filter as ShippingMethodFilterValidationRules;
use CG\Order\Shared\Shipping\Method\Entity as ShippingMethodEntity;
use CG\Order\Shared\Shipping\Method\Mapper as ShippingMethodMapper;
use CG\Order\Service\Shipping\Method\Service as ShippingMethodService;

return array(
    '/' => array (
        'controllers' => function() use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(Root::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method()
                );
            },
        'via' => array('GET', 'OPTIONS'),
        'name' => 'Root'
    ),
    '/order' => array (
        'controllers' => function() use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();
                $controller = $di->get(OrderCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => array('GET', 'PATCH', 'OPTIONS'),
        'name' => 'OrderCollection',
        'validation' => array("dataRules" => null, "filterRules" => OrderFilterValidationRules::class, "flatten" => false),
        'version' => new Version(1, 10),
        'entityRoute' => '/order/:orderId'
    ),
    '/order/:orderId' => array (
        'controllers' => function($orderId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(Order::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($orderId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'),
        'name' => 'OrderEntity',
        'validation' => array("dataRules" => OrderEntityValidationRules::class, "filterRules" => null, "flatten" => false),
        'version' => new Version(1, 10),
        'eTag' => [
            'mapperClass' => OrderMapper::class,
            'entityClass' => OrderEntity::class,
            'serviceClass' => OrderService::class
        ],
    ),
    '/order/:orderId/note' => array (
        'controllers' => function($orderId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(NoteCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($orderId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'POST', 'OPTIONS'),
        'name' => 'OrderNoteCollection',
        'entityRoute' => '/order/:orderId/note/:noteId',
        'validation' => array("dataRules" => NoteEntityValidationRules::class, "filterRules" => NoteFilterValidationRules::class, "flatten" => false)
    ),
    '/order/:orderId/note/:noteId' => array (
        'controllers' => function($orderId, $noteId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(Note::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($orderId, $noteId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'PUT', 'DELETE', 'OPTIONS'),
        'name' => 'OrderNoteEntity',
        'validation' => array("dataRules" => NoteEntityValidationRules::class, "filterRules" => null, "flatten" => false),
        'eTag' => [
            'mapperClass' => NoteMapper::class,
            'entityClass' => NoteEntity::class,
            'serviceClass' => NoteService::class
        ]
    ),
    '/order/:orderId/tracking' => array (
        'controllers' => function($orderId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(OrderTrackingCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($orderId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'POST', 'OPTIONS'),
        'entityRoute' => '/order/:orderId/tracking/:trackingId',
        'name' => 'OrderTrackingCollection',
        'validation' => array("dataRules" => OrderTrackingEntityValidationRules::class, "filterRules" => OrderTrackingFilterValidationRules::class, "flatten" => false),
        'version' => new Version(1, 2),
    ),
    '/order/:orderId/tracking/:trackingId' => array (
        'controllers' => function($orderId, $trackingId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(OrderTracking::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($orderId, $trackingId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'PUT', 'DELETE', 'OPTIONS'),
        'name' => 'OrderTrackingEntity',
        'validation' => array("dataRules" => OrderTrackingEntityValidationRules::class, "filterRules" => null, "flatten" => false),
        'version' => new Version(1, 2),
        'eTag' => [
            'mapperClass' => TrackingMapper::class,
            'entityClass' => TrackingEntity::class,
            'serviceClass' => TrackingService::class
        ]
    ),
    '/order/:orderId/alert' => array (
        'controllers' => function($orderId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(AlertCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($orderId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'POST', 'OPTIONS'),
        'entityRoute' => '/order/:orderId/alert/:alertId',
        'name' => 'OrderAlertCollection',
        'validation' => array("dataRules" => AlertEntityValidationRules::class, "filterRules" => AlertFilterValidationRules::Class, "flatten" => false)
    ),
    '/order/:orderId/alert/:alertId' => array (
        'controllers' => function($orderId, $alertId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(Alert::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($orderId, $alertId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'PUT', 'DELETE', 'OPTIONS'),
        'name' => 'OrderAlertEntity',
        'validation' => array("dataRules" => AlertEntityValidationRules::class, "filterRules" => null, "flatten" => false),
        'eTag' => [
            'mapperClass' => AlertMapper::class,
            'entityClass' => AlertEntity::class,
            'serviceClass' => AlertService::class
        ]
    ),
    '/order/:orderId/archive' => array (
        'controllers' => function($orderId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(Archive::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($orderId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'PUT', 'OPTIONS'),
        'name' => 'OrderArchiveEntity',
        'validation' => array("dataRules" => ArchiveEntityValidationRules::class, "filterRules" => null, "flatten" => false)
    ),
    '/order/:orderId/userChange' => array (
        'controllers' => function($orderId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(UserChange::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($orderId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'PUT', 'DELETE', 'OPTIONS'),
        'name' => 'OrderUserChangeEntity',
        'validation' => array("dataRules" => UserChangeEntityValidationRules::class, "filterRules" => null, "flatten" => false),
        'eTag' => [
            'mapperClass' => UserChangeMapper::class,
            'entityClass' => UserChangeEntity::class,
            'serviceClass' => UserChangeService::class
        ]
    ),
    '/orderItem' => array (
        'controllers' => function() use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();
                $controller = $di->get(ItemCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request->getBody())
                );
            },
        'via' => array('GET', 'PATCH', 'OPTIONS'),
        'name' => 'OrderItemCollection',
        'validation' => array("dataRules" => null, "filterRules" => ItemFilterValidationRules::class, "flatten" => false),
        'version' => new Version(1, 9),
        'entityRoute' => '/orderItem/:orderItemId'
    ),
    '/orderItem/:orderItemId' => array (
        'controllers' => function($orderItemId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(Item::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($orderItemId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'PUT', 'OPTIONS', 'DELETE', 'PATCH'),
        'name' => 'OrderItemEntity',
        'validation' => array("dataRules" => ItemEntityValidationRules::class, "filterRules" => null, "flatten" => false),
        'version' => new Version(1, 9),
        'eTag' => [
            'mapperClass' => ItemMapper::class,
            'entityClass' => ItemEntity::class,
            'serviceClass' => ItemService::class
        ]
    ),
    '/orderItem/:orderItemId/fee' => array (
        'controllers' => function($orderItemId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(FeeCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($orderItemId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'POST', 'OPTIONS'),
        'name' => 'OrderItemFeeCollection',
        'entityRoute' => '/orderItem/:orderItemId/fee/:feeId',
        'validation' => array("dataRules" => FeeEntityValidationRules::class, "filterRules" => FeeFilterValidationRules::class, "flatten" => false)
    ),
    '/orderItem/:orderItemId/fee/:feeId' => array (
        'controllers' => function($orderItemId, $feeId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(Fee::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($orderItemId, $feeId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'PUT', 'DELETE', 'OPTIONS'),
        'name' => 'OrderItemFeeEntity',
        'validation' => array("dataRules" => FeeEntityValidationRules::class, "filterRules" => null, "flatten" => false),
        'eTag' => [
            'mapperClass' => FeeMapper::class,
            'entityClass' => FeeEntity::class,
            'serviceClass' => FeeService::class
        ]
    ),
    '/orderItem/:orderItemId/giftWrap' => array (
        'controllers' => function($orderItemId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(GiftWrapCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($orderItemId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'POST', 'OPTIONS'),
        'entityRoute' => '/orderItem/:orderItemId/giftWrap/:giftWrapId',
        'name' => 'OrderItemGiftWrapCollection',
        'validation' => array("dataRules" => GiftWrapEntityValidationRules::class, "filterRules" => GiftWrapFilterValidationRules::class, "flatten" => false)
    ),
    '/orderItem/:orderItemId/giftWrap/:giftWrapId' => array (
        'controllers' => function($orderItemId, $giftWrapId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(GiftWrap::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($orderItemId, $giftWrapId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'PUT', 'DELETE', 'OPTIONS'),
        'name' => 'OrderItemGiftWrapEntity',
        'validation' => array("dataRules" => GiftWrapEntityValidationRules::class, "filterRules" => null, "flatten" => false),
        'eTag' => [
            'mapperClass' => GiftWrapMapper::class,
            'entityClass' => GiftWrapEntity::class,
            'serviceClass' => GiftWrapService::class
        ]
    ),
    '/orderBatch' => array (
        'controllers' => function() use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();
                $controller = $di->get(BatchCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method()
                );
            },
        'via' => array('GET', 'OPTIONS'),
        'entityRoute' => '/orderBatch/:batchId',
        'name' => 'OrderBatchCollection',
        'validation' => array("dataRules" => null, "filterRules" => BatchFilterValidationRules::class, "flatten" => false)
    ),
    '/orderBatch/:batchId' => array (
        'controllers' => function($batchId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(Batch::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($batchId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'PUT', 'DELETE', 'OPTIONS'),
        'name' => 'OrderBatchEntity',
        'validation' => array("dataRules" => BatchEntityValidationRules::class, "filterRules" => null, "flatten" => false),
        'eTag' => [
            'mapperClass' => BatchMapper::class,
            'entityClass' => BatchEntity::class,
            'serviceClass' => BatchService::class
        ]
    ),
    '/userPreference' => array (
        'controllers' => function() use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();
                $controller = $di->get(UserPreferenceCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method()
                );
            },
        'via' => array('GET', 'OPTIONS'),
        'entityRoute' => '/userPreference/:userId',
        'name' => 'UserPreferenceCollection',
        'validation' => array("dataRules" => null, "filterRules" => UserPreferenceFilterValidationRules::class, "flatten" => false),
        'eTag' => [
            'enabled' => false
        ]
    ),
    '/userPreference/:userId' => array (
        'controllers' => function($userId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(UserPreference::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($userId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'PUT', 'DELETE', 'OPTIONS'),
        'name' => 'UserPreferenceEntity',
        'validation' => array("dataRules" => UserPreferenceEntityValidationRules::class, "filterRules" => null, "flatten" => false),
        'eTag' => [
            'enabled' => false
        ]
    ),
    '/orderTag' => array (
        'controllers' => function() use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(TagCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method()
                );
            },
        'via' => array('GET', 'OPTIONS'),
        'entityRoute' => '/orderTag/:tagId',
        'name' => 'TagCollection',
        'validation' => array("dataRules" => null, "filterRules" => TagFilterValidationRules::class, "flatten" => false)
    ),
    '/orderTag/:tagId' => array (
        'controllers' => function($tagId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(Tag::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($tagId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'PUT', 'DELETE', 'OPTIONS'),
        'name' => 'TagEntity',
        'validation' => array("dataRules" => TagEntityValidationRules::class, "filterRules" => null, "flatten" => false),
        'eTag' => [
            'enabled' => false
        ]
    ),
    '/orderFilter' => array (
        'controllers' => function() use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(FilterCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => array('POST', 'OPTIONS'),
        'entityRoute' => '/orderFilter/:filterId',
        'name' => 'FilterCollection',
        'validation' => array("dataRules" => null, "filterRules" => null, "flatten" => false)
    ),
    '/orderFilter/:filterId' => array (
        'controllers' => function($filterId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(Filter::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($filterId)
                );
            },
        'via' => array('GET', 'OPTIONS'),
        'name' => 'FilterEntity',
        'validation' => array("dataRules" => null, "filterRules" => null, "flatten" => false),
        'eTag' => [
            'enabled' => false
        ]
    ),
    '/orderLabel' => [
        'controllers' => function() use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(LabelCollectionController::class, []);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'entityRoute' => '/orderLabel/:labelId',
        'name' => 'OrderLabelCollection',
        'version' => new Version(1, 3),
        'validation' => ["dataRules" => LabelEntityValidationRules::class, "filterRules" => LabelFilterValidationRules::class, "flatten" => false]
    ],
    '/orderLabel/:labelId' => [
        'controllers' => function($labelId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(LabelController::class, []);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($labelId, $app->request()->getBody())
                );
            },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'OrderLabelEntity',
        'version' => new Version(1, 3),
        'validation' => ["dataRules" => LabelEntityValidationRules::class, "filterRules" => null, "flatten" => false],
        'eTag' => [
            'mapperClass' => LabelMapper::class,
            'entityClass' => LabelEntity::class,
            'serviceClass' => LabelService::class
        ]
    ],
    '/shippingMethod' => [
        'controllers' => function() use ($app, $di) {
                $method = $app->request()->getMethod();

                $controller = $di->get(ShippingMethodCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => ['GET', 'OPTIONS'],
        'entityRoute' => '/shippingMethod/:id',
        'name' => 'ShippingMethodCollection',
        'validation' => [
            "filterRules" => ShippingMethodFilterValidationRules::class,
            "flatten" => false
        ],
    ],
    '/shippingMethod/:id' => [
        'controllers' => function($shippingMethodId) use ($app, $di) {
                $method = $app->request()->getMethod();

                $controller = $di->get(ShippingMethod::class, []);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($shippingMethodId, $app->request()->getBody())
                );
            },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'ShippingMethodEntity',
        'validation' => ["dataRules" => null, "filterRules" => null, "flatten" => false],
        'eTag' => [
            'enabled' => false
        ]
    ],
    '/tracking' => [
        'controllers' => function() use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(TrackingCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'TrackingCollection',
        'validation' => [
            "filterRules" => TrackingFilterValidationRules::class,
        ],
        'version' => new Version(1, 2),
    ],
);
