<?php
namespace CG\InputValidation\Location;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use CG\Validation\Rules\PaginationTrait;
use Zend\Di\Di;

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
                        new ArrayOfIntegersValidator(new IntegerValidator(), ['name' => 'id'])
                    ]
                ],
                'organisationUnitId' => [
                    'name'       => 'organisationUnitId',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), ['name' => 'organisationUnitId'])
                    ]
                ]
            ]
        );
    }
}