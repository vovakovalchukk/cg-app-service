<?php
namespace CG\InputValidation\ProductPickingLocation;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
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
                'organisationUnitId' => [
                    'name' => 'organisationUnitId',
                    'required' => false,
                    'validators' => [new ArrayOfIntegersValidator(
                        new IntegerValidator(['name' => 'organisationUnitId']),
                        'organisationUnitId'
                    )]
                ],
                'level' => [
                    'name' => 'level',
                    'required' => false,
                    'validators' => [new ArrayOfIntegersValidator(
                        new IntegerValidator(['name' => 'level']),
                        'level'
                    )]
                ],
            ]
        );
    }
}