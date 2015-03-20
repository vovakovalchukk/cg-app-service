<?php
namespace CG\InputValidation\Product\VariationMap;

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
            'productId' => [
                'name'       => 'productId',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'id']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'id must be at least %min%'])
                ]
            ],
            'name' => [
                'name'       => 'name',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'productAttributeName' => [
                'name'       => 'productAttributeName',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'listingId' => [
                'name'       => 'id',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'id']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'id must be at least %min%'])
                ]
            ]
        ];
    }
}