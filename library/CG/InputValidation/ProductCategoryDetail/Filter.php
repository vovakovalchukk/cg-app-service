<?php
namespace CG\InputValidation\ProductCategoryDetail;

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
        return array_merge(
            $this->getPaginationValidation(),
            [
                'id' => [
                    'name' => 'id',
                    'required' => false,
                    'validators' => [
                        new IsArrayValidator(['name' => 'id'])
                    ]
                ],
                'productId' => [
                    'name' => 'productId',
                    'required' => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'productId')
                    ]
                ],
                'categoryId' => [
                    'name' => 'categoryId',
                    'required' => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'categoryId')
                    ]
                ],
                'channel' => [
                    'name' => 'channel',
                    'required' => false,
                    'validators' => [
                        new IsArrayValidator(['name' => 'channel'])
                    ]
                ],
                'organisationUnitId' => [
                    'name' => 'organisationUnitId',
                    'required' => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                    ]
                ],
            ]
        );
    }
}