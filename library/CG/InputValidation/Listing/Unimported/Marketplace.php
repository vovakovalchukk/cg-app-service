<?php
namespace CG\InputValidation\Listing\Unimported;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\StringLength;

class Marketplace implements RulesInterface
{
    public function getRules()
    {
        return [
            'organisationUnitId' => [
                'name'       => 'organisationUnitId',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                ]
            ],
            'channel' => [
                'name'       => 'channel',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'accountId' => [
                'name'       => 'accountId',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'accountId')
                ]
            ],
        ];
    }
} 
