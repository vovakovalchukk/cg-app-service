<?php
namespace CG\InputValidation\ListingTemplate;

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
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'id')
                    ]
                ],
                'organisationUnitId' => [
                    'name' => 'organisationUnitId',
                    'required' => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                    ]
                ],
                'channel' => [
                    'name'       => 'channel',
                    'required'   => false,
                    'validators' => [
                        new IsArrayValidator(['name' => 'channel'])
                    ]
                ],
            ]
        );
    }
}