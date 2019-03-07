<?php
namespace CG\InputValidation\ProductPickingLocation;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;
use Zend\Validator\Regex;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return [
            'id' => [
                'name' => 'id',
                'required' => true,
                'validators' => [new Regex('/^\d+-\d+$/')]
            ],
            'organisationUnitId' => [
                'name' => 'organisationUnitId',
                'required' => true,
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ],
            'level' => [
                'name' => 'level',
                'required' => true,
                'validators' => [
                    new IntegerValidator(['name' => 'level']),
                    (new GreaterThan(['min' => 0, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'level must be at least %min%'])
                ]
            ],
            'names' => [
                'name' => 'names',
                'required' => true,
                'validators' => [new IsArrayValidator(['name' => 'names'])]
            ],
        ];
    }
}