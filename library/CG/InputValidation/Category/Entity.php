<?php
namespace CG\InputValidation\Category;

use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;
use Zend\Validator\StringLength;

class Entity implements RulesInterface
{
    public function getRules()
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
            'externalId' => [
                'name'       => 'externalId',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'parentId' => [
                'name'       => 'parentId',
                'required'   => false,
                'validators' => [
                    new IntegerValidator(['name' => 'parentId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'parentId must be at least %min%'])
                ]
            ],
            'title' => [
                'name'       => 'title',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'description' => [
                'name'       => 'description',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'channel' => [
                'name'       => 'channel',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'marketplace' => [
                'name'       => 'marketplace',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'listable' => [
                'name'       => 'listable',
                'required'   => false,
                'validators' => [new BooleanValidator(['name' => 'listable'])]
            ],
            'enabled' => [
                'name'       => 'enabled',
                'required'   => false,
                'validators' => [new BooleanValidator(['name' => 'enabled'])]
            ],
            'version' => [
                'name'       => 'version',
                'required'   => false,
                'validators' => [
                    new IntegerValidator(['name' => 'version']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'parentId must be at least %min%'])
                ]
            ],
        ];
    }
}