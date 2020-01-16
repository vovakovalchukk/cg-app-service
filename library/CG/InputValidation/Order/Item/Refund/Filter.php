<?php
namespace CG\InputValidation\Order\Item\Refund;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\RulesInterface;

class Filter implements RulesInterface
{
    use PaginationTrait;

    public function getRules()
    {
        $integerValidator = new IntegerValidator();
        $rules = [
            'id' => [
                'name' => 'id',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator($integerValidator, 'id')
                ]
            ],
            'organisationUnitId' => [
                'name' => 'organisationUnitId',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator($integerValidator, 'organisationUnitId')
                ]
            ],
            'itemId' => [
                'name' => 'itemId',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'id'])
                ]
            ],
        ];

        return array_merge($this->getPaginationValidation(), $rules);
    }
}