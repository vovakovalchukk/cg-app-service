<?php
use CG\ExchangeRate\Service as ExchangeRateService;
use Zend\Di\Di;

/** @var Di $di */
return [
    'currency:fetchExchangeRates' => [
        'command' => function () use ($di) {
            $command = $di->get(ExchangeRateService::class);
            $command->fetchCurrentExchangeRates('GBP');
        },
        'description' => "Fetch end-of-day exchange rates for all currencies from Open Exchange Rates",
        'arguments' => [
        ],
        'options' => [
        ],
    ],
];
