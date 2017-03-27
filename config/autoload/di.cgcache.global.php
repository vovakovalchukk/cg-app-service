<?php
use CG\Cache\InvalidationHandler;
use CG\Order\Shared\Shipping\Method\Entity as ShippingMethod;

return [
    'di' => [
        'instance' => [
            InvalidationHandler::class => [
                'parameters' => [
                    'validateCollectionChance' => [
                        InvalidationHandler::VALIDATE_COLLECTION_CHANCE_DEFAULT_KEY => 50,
                        ShippingMethod::class => 0,
                    ],
                ],
            ],
        ],
    ]
];