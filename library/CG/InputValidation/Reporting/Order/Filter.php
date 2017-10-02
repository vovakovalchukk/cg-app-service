<?php
namespace CG\InputValidation\Reporting\Order;

use CG\InputValidation\Order\Order\Filter as OrderFilter;
use CG\Validation\Rules\ValidatorTrait;
use CG\Validation\Rules\IsArrayValidator;
use Zend\Di\Di;

class Filter extends OrderFilter
{
    use ValidatorTrait;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

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
