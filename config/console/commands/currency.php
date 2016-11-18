<?php
use CG\ExchangeRate\Service as ExchangeRateService;
use Zend\Di\Di;

/** @var Di $di */
return [
    'currency:fetchExchangeRates' => [
        'command' => function () use ($di) {
            $command = $di->get(ExchangeRateService::class);
            $endOfDay = date('Y-m-d H:i:s');
            $command->fetchAllExchangeRates($endOfDay);
        },
        'description' => "Fetch end-of-day exchange rates for all currencies from Open Exchange Rates",
        'arguments' => [
        ],
        'options' => [
        ],
    ],
];
