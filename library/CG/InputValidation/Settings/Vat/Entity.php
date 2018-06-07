<?php
namespace CG\InputValidation\Settings\Vat;

use CG\Validation\RulesInterface;
use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\IntegerValidator;
use Zend\Validator\GreaterThan;
use Zend\Validator\StringLength;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return [
            'id' => [
                'name'       => 'id',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'id']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'id must be at least %min%'])
                ]
            ],
            'chargeVat' => [
                'name' => 'chargeVat',
                'required' => false,
                'validators' => [new BooleanValidator()]
            ],
        ];
    }
}