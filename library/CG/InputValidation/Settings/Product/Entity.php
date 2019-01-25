<?php
namespace CG\InputValidation\Settings\Product;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;
use Zend\Validator\StringLength;
use CG\Validation\Rules\BooleanValidator;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return [
            'id' => [
                'name'       => 'id',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'id']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'id must be at least %min%'])
                ]
            ],
            'defaultStockMode' => [
                'name' => 'defaultStockMode',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'defaultStockLevel' => [
                'name'       => 'defaultStockLevel',
                'required'   => false,
                'validators' => [new IntegerValidator(['name' => 'defaultStockLevel'])]
            ],
            'lowStockThresholdOn' => [
                'name'       => 'lowStockThresholdOn',
                'required'   => false,
                'validators' => [new BooleanValidator(['name' => 'lowStockThresholdOn'])]
            ],
            'lowStockThresholdValue' => [
                'name'       => 'lowStockThresholdValue',
                'required'   => false,
                'validators' => [new IntegerValidator(['name' => 'lowStockThresholdValue'])]
            ]
        ];
    }
}
