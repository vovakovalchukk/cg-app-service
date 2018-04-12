<?php
namespace CG\InputValidation\ProductCategoryDetail;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;
use Zend\Validator\StringLength;
use CG\Validation\Rules\IsArrayValidator;

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
            'categoryId' => [
                'name' => 'categoryId',
                'required' => true,
                'validators' => [
                    new IntegerValidator(['name' => 'categoryId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'categoryId must be at least %min%'])
                ]
            ],
            'channel' => [
                'name' => 'channel',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])]
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
            'external' => [
                'name' => 'external',
                'required' => false,
                'validators' => [new IsArrayValidator(['name' => 'external'])]
            ],
        ];
    }
}