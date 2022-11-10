<?php

namespace CG\InputValidation\Settings\Alias\Rule;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;

class PriorityChange
{
    public function getRules(): array
    {
        return [
            'ruleIds' => [
                'name' => 'ruleIds',
                'required' => true,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'ruleIds')
                ]
            ],
        ];
    }
}