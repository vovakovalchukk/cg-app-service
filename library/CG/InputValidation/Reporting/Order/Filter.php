<?php
namespace CG\InputValidation\Reporting\Order;

use CG\InputValidation\Order\Order\Filter as OrderFilter;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\ValidatorTrait;

class Filter extends OrderFilter
{
    use ValidatorTrait;

    public function getRules()
    {
        return array_merge(
            parent::getRules(),
            [
                'metric' => [
                    'name' => 'metric',
                    'required' => true,
                    'validators' => [
                        new IsArrayValidator(["name" => "metric"])
                    ]
                ]
            ]
        );
    }
}
