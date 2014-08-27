<?php
use Slim\Slim;
use CG\Slim\Versioning\Version;
use CG\Controllers\Settings;
use CG\Controllers\Settings\Invoice;
use CG\Controllers\Settings\Invoice\Collection as InvoiceCollection;

use CG\InputValidation\Settings\Invoice\Filter as CollectionValidation;
use CG\InputValidation\Settings\Invoice\Entity as EntityValidation;

use CG\Controllers\Settings\Shipping\Alias as Alias;
use CG\Controllers\Settings\Shipping\Alias\Collection as AliasCollection;

use CG\InputValidation\Settings\Alias\Filter as AliasCollectionValidation;
use CG\InputValidation\Settings\Alias\Entity as AliasEntityValidation;

use CG\Settings\Invoice\Shared\Entity as InvoiceEntity;
use CG\Settings\Invoice\Shared\Mapper as InvoiceMapper;
use CG\Settings\Invoice\Service\Service as InvoiceService;

use CG\Settings\Shipping\Alias\Entity as AliasEntity;
use CG\Settings\Shipping\Alias\Mapper as AliasMapper;
use CG\Settings\Shipping\Alias\Service as AliasService;

use CG\Controllers\Settings\Clearbooks;
use CG\Settings\Clearbooks\Customer\Entity as ClearbooksCustomerEntity;
use CG\Settings\Clearbooks\Customer\Mapper as ClearbooksCustomerMapper;
use CG\Settings\Clearbooks\Customer\Service as ClearbooksCustomerService;
use CG\Controllers\Settings\Clearbooks\Customer as ClearbooksCustomerController;
use CG\Controllers\Settings\Clearbooks\Customer\Collection as ClearbooksCustomerCollection;
use CG\InputValidation\Settings\Clearbooks\Customer\Filter as ClearbooksCustomerCollectionValidation;
use CG\InputValidation\Settings\Clearbooks\Customer\Entity as ClearbooksCustomerEntityValidation;

return [
    '/settings' => [
        'controllers' => function() use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(Settings::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'Settings'
    ],
    '/settings/invoice' => [
        'controllers' => function() use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(InvoiceCollection::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'InvoiceSettingsCollection',
        'validation' => [
            'filterRules' => CollectionValidation::class
        ]
    ],
    '/settings/invoice/:id' => [
        'controllers' => function($invoiceId) use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(Invoice::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($invoiceId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'InvoiceSettings',
        'validation' => [
            "dataRules" => EntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => InvoiceMapper::class,
            'entityClass' => InvoiceEntity::class,
            'serviceClass' => InvoiceService::class
        ]
    ],
    '/settings/shipping' => [
        'via' => ['GET'],
        'controllers' => function() use ($di, $app) {
                $method = $app->request()->getMethod();
                $controller = $di->get(Settings\Shipping::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'name' => 'clearbooks'
    ],
    '/settings/shipping/alias' => [
        'controllers' => function() use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(AliasCollection::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
        },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'AliasSettingsCollection',
        'validation' => [
            'filterRules' => AliasCollectionValidation::class,
            'dataRules' => AliasEntityValidation::class
        ],
        'version' => new Version(1, 2),
    ],
    '/settings/shipping/alias/:aliasId' => [
        'controllers' => function($aliasId) use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(Alias::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($aliasId, $app->request()->getBody())
                );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'AliasSettingsEntity',
        'validation' => [
            "dataRules" => AliasEntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => AliasMapper::class,
            'entityClass' => AliasEntity::class,
            'serviceClass' => AliasService::class
        ],
        'version' => new Version(1, 2),
    ],
    '/settings/clearbooks' => [
        'via' => ['GET'],
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(Clearbooks::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'name' => 'clearbooks'
    ],
    '/settings/clearbooks/customer' => [
        'controllers' => function() use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(ClearbooksCustomerCollection::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
        },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'ClearbooksCustomerSettingsCollection',
        'validation' => [
            'filterRules' => ClearbooksCustomerCollectionValidation::class,
            'dataRules' => ClearbooksCustomerEntityValidation::class
        ],
        'version' => new Version(1,1),
    ],
    '/settings/clearbooks/customer/:ClearbooksCustomerId' => [
        'controllers' => function($ClearbooksCustomerId) use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(ClearbooksCustomerController::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($ClearbooksCustomerId, $app->request()->getBody())
                );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'ClearbooksCustomerSettingsEntity',
        'validation' => [
            "dataRules" => ClearbooksCustomerEntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => ClearbooksCustomerMapper::class,
            'entityClass' => ClearbooksCustomerEntity::class,
            'serviceClass' => ClearbooksCustomerService::class
        ],
        'version' => new Version(1,1),
    ],
];