<?php
namespace CG\InputValidation\ProductSort;

use CG\Validation\Rules\BooleanValidator;
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
                'validators' => [
                    new IntegerValidator(['name' => 'id']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'id must be at least %min%'])
                ]
            ],
            'organisationUnitId' => [
                'name'       => 'organisationUnitId',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ],
            ],
            'userId' => [
                'name' => 'userId',
                'required' => false,
                'validators' => [
                    new IntegerValidator(['name' => 'userId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'userId must be at least %min%'])
                ],
            ],
            'filters' => [
                'name' => 'filters',
                'required' => true,
                'allow_empty' => false,
                'validators' => [new StringLength(['min' => 1])],
            ],
            'defaultFilter' => [
                'name'       => 'defaultFilter',
                'required'   => false,
                'allow_empty' => true,
                'validators' => [new BooleanValidator(['name' => 'defaultFilter'])],
            ],
        ];
    }
}
