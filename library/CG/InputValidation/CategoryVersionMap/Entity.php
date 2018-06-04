<?php
namespace CG\InputValidation\CategoryVersionMap;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return [
            'id' => [
                'name' => 'id',
                'required' => true,
                'validators' => [
                    new IntegerValidator(['name' => 'id']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'id must be at least %min%'])
                ]
            ],
            'versionMap' => [
                'name' => 'versionMap',
                'required' => true,
                'validators' => [new IsArrayValidator(['name' => 'versionMap'])]
            ],
        ];
    }
}