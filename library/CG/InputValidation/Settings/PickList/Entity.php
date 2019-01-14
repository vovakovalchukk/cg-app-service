<?php
namespace CG\InputValidation\Settings\PickList;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\IsArrayValidator;
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
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'id']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'id must be at least %min%'])
                ]
            ],
            'sortField' => [
                'name'       => 'sortField',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'sortDirection' => [
                'name'       => 'sortDirection',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'showPictures' => [
                'name'       => 'showPictures',
                'required'   => false,
                'allow_empty' => true,
                'validators' => [new BooleanValidator(['name' => 'showPictures'])]
            ],
            'showSkuless' => [
                'name'       => 'showSkuless',
                'required'   => false,
                'allow_empty' => true,
                'validators' => [new BooleanValidator(['name' => 'showSkuless'])]
            ],
            'locationNames' => [
                'name' => 'locationNames',
                'required' => false,
                'allow_empty' => true,
                'validators' => [new IsArrayValidator(['name' => 'locationNames'])]
            ],
        ];
    }
}
