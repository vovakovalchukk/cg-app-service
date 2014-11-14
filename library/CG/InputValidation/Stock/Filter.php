<?php
namespace CG\InputValidation\Stock;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\Rules\IntegerValidator;

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
                    'name' => 'sku',
                    'required' => false,
                    'validators' => [
                        new IsArrayValidator(["name" => "sku"])
                    ]
                ],
                'locationId' => [
                    'name'       => 'locationId',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'locationId')
                    ]
                ],
            ]
        );
    }
}