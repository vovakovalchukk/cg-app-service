<?php
use CG\ExchangeRate\Service as ExchangeRateService;
use CG\Stdlib\Date;
use Symfony\Component\Console\Input\InputInterface;
use Zend\Di\Di;

/** @var Di $di */
return [
    'currency:fetchExchangeRates' => [
        'command' => function (InputInterface $input) use ($di) {
            $inputDate = $input->getArgument('date');

            $date = new Date();
            $date->modify('-1 day');
            if (!is_null($inputDate)) {
                $date = new Date($inputDate);
            }

            $command = $di->get(ExchangeRateService::class);
            $command->fetchAllExchangeRates($date);
        },
        'description' => "Fetch end-of-day exchange rates for all currencies from Open Exchange Rates",
        'arguments' => [
            'date' => [
                'required' => false,
                'default' => null
            ],
        ],
        'options' => [
        ],
    ],

    'currency:test' => [
        'command' => function () use ($di) {
//            $inputDate = $input->getArgument('date');
//
//            $date = new Date();
//            $date->modify('-1 day');
//            if (!is_null($inputDate)) {
//                $date = new Date($inputDate);
//            }

            /* @var $command ExchangeRateService */
            $command = $di->get(ExchangeRateService::class);
            $res = $command->fetch('2017-01-01_PLN_USD');

            print_r($res);
        },
        'description' => "Fetch end-of-day exchange rates for all currencies from Open Exchange Rates",
        'arguments' => [
        ],
        'options' => [
        ],
    ],

    'currency:convertCurrency' => [
        'command' => function () use ($di) {
//            $inputDate = $input->getArgument('date');
//
            $date = new Date('2017-12-20');
//            $date->modify('-2 day');
//            if (!is_null($inputDate)) {
//                $date = new Date($inputDate);
//            }

            /* @var $command ExchangeRateService */
            $command = $di->get(ExchangeRateService::class);
//            $res = $command->fetchConversionRate('PLN', 'GBP', $date);

//            $command->fetch('2018-01-03_PLN_USD');

            sleep(1);

            $command->removeById('2018-01-03_PLN_USD');

//            print_r($res);
        },
        'description' => "Fetch end-of-day exchange rates for all currencies from Open Exchange Rates",
        'arguments' => [
        ],
        'options' => [
        ],
    ],


];
