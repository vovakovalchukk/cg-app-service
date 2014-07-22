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
use CG\Controllers\Order\Tracking;
use CG\Controllers\Order\Tracking\Collection as TrackingCollection;
use CG\InputValidation\Order\Tracking\Entity as TrackingEntityValidationRules;
use CG\InputValidation\Order\Tracking\Filter as TrackingFilterValidationRules;

//Alert
use CG\Controllers\Order\Alert;
use CG\Controllers\Order\Alert\Collection as AlertCollection;
use CG\InputValidation\Order\Alert\Entity as AlertEntityValidationRules;
use CG\InputValidation\Order\Alert\Filter as AlertFilterValidationRules;

//Archive
use CG\Controllers\Order\Archive;
use CG\InputValidation\Order\Archive\Entity as ArchiveEntityValidationRules;

//Item
use CG\Controllers\Order\Item;
use CG\Controllers\Order\Item\Collection as ItemCollection;
use CG\InputValidation\Order\Item\Entity as ItemEntityValidationRules;
use CG\InputValidation\Order\Item\Filter as ItemFilterValidationRules;

//Fee
use CG\Controllers\Order\Item\Fee;
use CG\Controllers\Order\Item\Fee\Collection as FeeCollection;
use CG\InputValidation\Order\Item\Fee\Entity as FeeEntityValidationRules;
use CG\Slim\InputValidation\PageLimit as FeeFilterValidationRules;

//Note
use CG\Controllers\Order\Note;
use CG\InputValidation\Order\Note\Entity as NoteEntityValidationRules;
use CG\InputValidation\Order\Note\Filter as NoteFilterValidationRules;

//GiftWrap
use CG\Controllers\Order\Item\GiftWrap;
use CG\Controllers\Order\Item\GiftWrap\Collection as GiftWrapCollection;
use CG\InputValidation\Order\Item\GiftWrap\Entity as GiftWrapEntityValidationRules;
use CG\Slim\InputValidation\PageLimit as GiftWrapFilterValidationRules;

//UserChange
use CG\Controllers\Order\UserChange;
use CG\InputValidation\Order\UserChange\Entity as UserChangeEntityValidationRules;

//Batch
use CG\Controllers\Order\Batch;
use CG\Controllers\Order\Batch\Collection as BatchCollection;
use CG\InputValidation\Order\Batch\Entity as BatchEntityValidationRules;
use CG\InputValidation\Order\Batch\Filter as BatchFilterValidationRules;

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

//Filter
use CG\Controllers\Order\Filter;
use CG\Controllers\Order\Filter\Collection as FilterCollection;

//ShippingMethod
use CG\Controllers\Shipping\Method\Method as ShippingMethod;
use CG\Controllers\Shipping\Method\Method\Collection as ShippingMethodCollection;
use CG\InputValidation\Shipping\Method\Filter as ShippingMethodFilterValidationRules;

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
                    $controller->$method()
                );
            },
        'via' => array('GET', 'OPTIONS'),
        'name' => 'OrderCollection',
        'validation' => array("dataRules" => null, "filterRules" => OrderFilterValidationRules::class, "flatten" => false)
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
        'via' => array('GET', 'PUT', 'DELETE', 'OPTIONS'),
        'name' => 'OrderEntity',
        'validation' => array("dataRules" => OrderEntityValidationRules::class, "filterRules" => null, "flatten" => false),
        'eTag' => [
            'mapper' => OrderMapper::class,
            'entityClass' => OrderEntity::class,
            'service' => OrderService::class
        ]
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
        'validation' => array("dataRules" => NoteEntityValidationRules::class, "filterRules" => null, "flatten" => false)
    ),
    '/order/:orderId/tracking' => array (
        'controllers' => function($orderId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(TrackingCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($orderId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'POST', 'OPTIONS'),
        'name' => 'OrderTrackingCollection',
        'validation' => array("dataRules" => TrackingEntityValidationRules::class, "filterRules" => TrackingFilterValidationRules::class, "flatten" => false)
    ),
    '/order/:orderId/tracking/:trackingId' => array (
        'controllers' => function($orderId, $trackingId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(Tracking::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($orderId, $trackingId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'PUT', 'DELETE', 'OPTIONS'),
        'name' => 'OrderTrackingEntity',
        'validation' => array("dataRules" => TrackingEntityValidationRules::class, "filterRules" => null, "flatten" => false)
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
        'validation' => array("dataRules" => AlertEntityValidationRules::class, "filterRules" => null, "flatten" => false)
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
        'validation' => array("dataRules" => UserChangeEntityValidationRules::class, "filterRules" => null, "flatten" => false)
    ),
    '/orderItem' => array (
        'controllers' => function() use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();
                $controller = $di->get(ItemCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method()
                );
            },
        'via' => array('GET', 'OPTIONS'),
        'name' => 'OrderItemCollection',
        'validation' => array("dataRules" => null, "filterRules" => ItemFilterValidationRules::class, "flatten" => false),
        'version' => new Version(1, 2),
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
        'via' => array('GET', 'PUT', 'OPTIONS', 'DELETE'),
        'name' => 'OrderItemEntity',
        'validation' => array("dataRules" => ItemEntityValidationRules::class, "filterRules" => null, "flatten" => false),
        'version' => new Version(1, 2),
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
        'validation' => array("dataRules" => FeeEntityValidationRules::class, "filterRules" => null, "flatten" => false)
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
        'validation' => array("dataRules" => GiftWrapEntityValidationRules::class, "filterRules" => null, "flatten" => false)
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
        'validation' => array("dataRules" => BatchEntityValidationRules::class, "filterRules" => null, "flatten" => false)
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
        'name' => 'UserPreferenceCollection',
        'validation' => array("dataRules" => null, "filterRules" => UserPreferenceFilterValidationRules::class, "flatten" => false)
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
        'validation' => array("dataRules" => UserPreferenceEntityValidationRules::class, "filterRules" => null, "flatten" => false)
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
        'validation' => array("dataRules" => TagEntityValidationRules::class, "filterRules" => null, "flatten" => false)
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
        'validation' => array("dataRules" => null, "filterRules" => null, "flatten" => false)
    ),
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
    ]
);
