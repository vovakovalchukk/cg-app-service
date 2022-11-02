<?php

namespace CG\InputValidation\Settings\Alias\Rule;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;
use Zend\Validator\StringLength;

class Entity implements RulesInterface
{
    public function getRules(): array
    {
        return [
            'id' => [
                'name'       => 'id',
                'required'   => false,
                'validators' => []
            ],
            'shippingAliasId' => [
                'name'       => 'shippingAliasId',
                'required'   => false,
                'validators' => [
                    new IntegerValidator(['name' => 'shippingAliasId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'shippingAliasId must be at least %min%'])
                ]
            ],
            "type" => [
                'name'       => 'type',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            "operator" => [
                'name'       => 'operator',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            "value" => [
                'name'       => 'value',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'priority' => [
                'name' => 'priority',
                'required' => false,
                'validators' => [new IntegerValidator(['name' => 'priority'])]
            ],
        ];
    }
}