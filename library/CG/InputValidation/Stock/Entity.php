<?php
namespace CG\InputValidation\Stock;

use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;
use Zend\Validator\StringLength;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return [
            'id' => [
                'name'       => 'id',
                'required'   => false,
                'validators' => []
            ],
            'organisationUnitId' => [
                'name'       => 'organisationUnitId',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ],
            'sku' => [
                'name' => 'sku',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'stockMode' => [
                'name' => 'stockMode',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'stockLevel' => [
                'name'       => 'stockLevel',
                'required'   => false,
                'validators' => [new IntegerValidator(['name' => 'stockLevel'])]
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
