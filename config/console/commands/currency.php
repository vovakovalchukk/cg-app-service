<?php
use CG\ExchangeRate\Service as ExchangeRateService;
use CG\Stdlib\Date;
use Zend\Di\Di;

/** @var Di $di */
return [
    'currency:fetchExchangeRates' => [
        'command' => function () use ($di) {
            $command = $di->get(ExchangeRateService::class);
            $date = new Date();
            $date->modify('-1 day');
            $command->fetchAllExchangeRates($date);
        },
        'description' => "Fetch end-of-day exchange rates for all currencies from Open Exchange Rates",
        'arguments' => [
        ],
        'options' => [
        ],
    ],
];
