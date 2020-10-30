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
        $integerValidator = new IntegerValidator();
        return array_merge(
            $this->getPaginationValidation(),
            [
                'id' => [
                    'name'       => 'id',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator($integerValidator, 'id')
                    ]
                ],
                'organisationUnitId' => [
                    'name'       => 'organisationUnitId',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator($integerValidator, 'organisationUnitId')
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
                'categoryTemplateId' => [
                    'name' => 'categoryTemplateId',
                    'required' => false,
                    'validators' => [new ArrayOfIntegersValidator($integerValidator, 'categoryTemplateId')]
                ],
                'upc' => [
                    'name'=> 'upc',
                    'required'=> false,
                    'validators'=> [new IsArrayValidator(['name' => 'upc'])]
                ],
                'isbn' => [
                    'name'=> 'isbn',
                    'required'=> false,
                    'validators'=> [new IsArrayValidator(['name' => 'isbn'])]
                ],
                'gtin' => [
                    'name'=> 'isbn',
                    'required'=> false,
                    'validators'=> [new IsArrayValidator(['name' => 'gtin'])]
                ],
                'supplierId' => [
                    'name' => 'supplierId',
                    'required' => false,
                    'validators' => [new ArrayOfIntegersValidator($integerValidator, 'supplierId')]
                ],
                'hsTariffNumber' => [
                    'name'=> 'hsTariffNumber',
                    'required'=> false,
                    'validators'=> [new IsArrayValidator(['name' => 'hsTariffNumber'])]
                ],
            ]
        );
    }
}