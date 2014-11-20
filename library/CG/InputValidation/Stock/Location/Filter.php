<?php
namespace CG\InputValidation\Stock\Location;

use CG\Validation\Rules\ArrayOfIntegersValidator;
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
            ]
        );
    }
}