<?php
namespace CG\InputValidation\Product\VariationAttributeMap;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use CG\Validation\Rules\PaginationTrait;
use Zend\Validator\GreaterThan;

class Filter implements RulesInterface
{
    use PaginationTrait;

    public function getRules()
    {
        return array_merge(
            $this->getPaginationValidation(),
            [
                'id' => [
                    'name'       => 'id',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'id')
                    ]
                ],
                'productId' => [
                    'name'       => 'productId',
                    'required'   => false,
                    'validators' => [
                        new IntegerValidator(['name' => 'productId'])
                    ]
                ],
                'productAttributeName' => [
                    'name'       => 'productAttributeName',
                    'required'   => false,
                    'validators' => [
                        new IsArrayValidator(['name' => 'productAttributeName'])
                    ]
                ],
                'name' => [
                    'name'       => 'name',
                    'required'   => false,
                    'validators' => [
                        new IsArrayValidator(['name' => 'name'])
                    ]
                ]
            ]
        );
    }
}