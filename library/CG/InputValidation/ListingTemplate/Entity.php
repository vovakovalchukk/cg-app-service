<?php
namespace CG\InputValidation\ListingTemplate;

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
            'organisationUnitId' => [
                'name' => 'organisationUnitId',
                'required' => true,
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ],
            'channel' => [
                'name'       => 'channel',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'name' => [
                'name'       => 'name',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'template' => [
                'name'       => 'template',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
        ];
    }
}