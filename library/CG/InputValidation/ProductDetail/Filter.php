<?php
namespace CG\InputValidation\ProductDetail;

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
                    'name'       => 'id',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'id')
                    ]
                ],
                'organisationUnitId' => [
                    'name'       => 'organisationUnitId',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                    ]
                ],
                'sku' => [
                    'name'       => 'sku',
                    'required'   => false,
                    'validators' => [
                        new IsArrayValidator(['name' => 'sku'])
                    ]
                ],
                'ean' => [
                    'name'       => 'ean',
                    'required'   => false,
                    'validators' => [new IsArrayValidator(['name' => 'ean'])]
                ],
                'brand' => [
                    'name'       => 'brand',
                    'required'   => false,
                    'validators' => [new IsArrayValidator(['name' => 'brand'])]
                ],
                'mpn' => [
                    'name'       => 'mpn',
                    'required'   => false,
                    'validators' => [new IsArrayValidator(['name' => 'mpn'])]
                ],
                'asin' => [
                    'name'       => 'asin',
                    'required'   => false,
                    'validators' => [new IsArrayValidator(['name' => 'asin'])]
                ],
            ]
        );
    }
}