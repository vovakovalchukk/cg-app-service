<?php
use CG\Cache\InvalidationHandler;
use CG\Cache\KeyGenerator\Redis as KeyGenerator;
use CG\Order\Shared\Shipping\Method\Entity as ShippingMethod;
use CG\Stock\Location\Entity as StockLocation;
use CG\Stock\Location\LinkedLocation as LinkedStockLocation;
use CG\Stock\Location\QuantifiedLocation as QuantifiedStockLocation;
use CG\Stock\Location\TypedEntity as TypedStockLocation;

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
            KeyGenerator::class => [
                'parameters' => [
                    'classMap' => [
                        TypedStockLocation::class => StockLocation::class,
                        QuantifiedStockLocation::class => StockLocation::class,
                        LinkedStockLocation::class => StockLocation::class,
                    ],
                ],
            ],
        ],
    ]
];