<?php
namespace CG\InputValidation\ProductDetail;

use CG\Validation\Rules\DecimalValidator;
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
                'validators' => [
                    new IntegerValidator(['name' => 'id']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'id must be at least %min%'])
                ]
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
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'weight' => [
                'name'       => 'weight',
                'required'   => false,
                'validators' => [new DecimalValidator(['name' => 'weight'])]
            ],
            'width' => [
                'name'       => 'width',
                'required'   => false,
                'validators' => [new DecimalValidator(['name' => 'width'])]
            ],
            'height' => [
                'name'       => 'height',
                'required'   => false,
                'validators' => [new DecimalValidator(['name' => 'height'])]
            ],
            'length' => [
                'name'       => 'length',
                'required'   => false,
                'validators' => [new DecimalValidator(['name' => 'length'])]
            ],
        ];
    }
}