<?php
namespace CG\InputValidation\ProductAccountDetail;

use CG\Validation\Rules\DecimalValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;
use Zend\Validator\StringLength;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return [
            'id' => [
                'name' => 'id',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'productId' => [
                'name' => 'productId',
                'required' => true,
                'validators' => [
                    new IntegerValidator(['name' => 'productId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'productId must be at least %min%'])
                ]
            ],
            'accountId' => [
                'name' => 'accountId',
                'required' => true,
                'validators' => [
                    new IntegerValidator(['name' => 'accountId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'accountId must be at least %min%'])
                ]
            ],
            'organisationUnitId' => [
                'name' => 'organisationUnitId',
                'required' => true,
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ],
            'price' => [
                'name' => 'price',
                'required' => false,
                'validators' => [new DecimalValidator(['name' => 'external'])]
            ],
            'externalType' => [
                'name' => 'externalType',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'externalData' => [
                'name' => 'externalData',
                'required' => false,
                'validators' => [new IsArrayValidator(['name' => 'externalData'])]
            ],
        ];
    }
}