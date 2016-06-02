<?php
namespace CG\InputValidation\Settings\SetupProgress;

use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;

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
            'steps' => [
                'name' => 'steps',
                'required' => true,
                'validators' => [new IsArrayValidator(['name' => 'steps'])]
            ],
        ];
    }
}
