<?php
namespace CG\InputValidation\Settings\Alias;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use Zend\Validator\GreaterThan;
use CG\Validation\RulesInterface;
use Zend\Validator\StringLength;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return [
            'id' => [
                'name'       => 'id',
                'required'   => false,
                'validators' => []
            ],
            'name' => [
                'name'       => 'name',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'organisationUnitId' => [
                'name'       => 'organisationUnitId',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ],
            "accountId" => [
                'name'      => 'accountId',
                'required'   => false,
                'validators' => [
                    new IntegerValidator(['name' => 'accountId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'accountId must be at least %min%'])
                ]
            ],
            "shippingService" => [
                'name'       => 'shippingService',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'methodIds' => [
                'name' => 'methodIds',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'methodIds')
                ]
            ],
            "options" => [
                'name'       => 'options',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
        ];
    }
}