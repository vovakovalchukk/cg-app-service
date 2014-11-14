<?php
namespace CG\InputValidation\Settings\Clearbooks\Customer;

use CG\Validation\Rules\IntegerValidator;
use Zend\Validator\GreaterThan;
use CG\Validation\RulesInterface;

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
            'organisationUnitId' => [
                'name'       => 'organisationUnitId',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ],
            "clearbooksCustomerId" => [
                'name'      => 'clearbooksCustomerId',
                'required'   => true,
                'validators' => [new IntegerValidator(['name' => 'clearbooksCustomerId'])]
            ]
        ];
    }
}