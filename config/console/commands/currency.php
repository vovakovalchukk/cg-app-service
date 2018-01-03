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
            $res = $command->fetch(1);

            print_r($res);
        },
        'description' => "Fetch end-of-day exchange rates for all currencies from Open Exchange Rates",
        'arguments' => [
        ],
        'options' => [
        ],
    ],


];
