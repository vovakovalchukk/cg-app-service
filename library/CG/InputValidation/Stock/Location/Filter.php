<?php
namespace CG\InputValidation\Stock\Location;

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
                'stockId' => [
                    'name'       => 'stockId',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'stockId')
                    ]
                ],
                'locationId' => [
                    'name'       => 'locationId',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'locationId')
                    ]
                ],
                'ouIdSku' => [
                    'name'       => 'ouIdSku',
                    'required'   => false,
                    'validators' => [
                        new IsArrayValidator(['name' => 'ouIdSku'])
                    ]
                ],
            ]
        );
    }
}