<?php
namespace CG\InputValidation\Settings\PickList;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\BooleanValidator;
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
            'column' => [
                'name'       => 'column',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'orderBy' => [
                'name'       => 'orderBy',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'showPictures' => [
                'name'       => 'showPictures',
                'required'   => false,
                'validators' => [new BooleanValidator(['name' => 'showPictures'])]
            ],
            'showSkuless' => [
                'name'       => 'showSkuless',
                'required'   => false,
                'validators' => [new BooleanValidator(['name' => 'showSkuless'])]
            ]
        ];
    }
}
