<?php
namespace CG\InputValidation\UserPreference;

use CG\Validation\RulesInterface;
use CG\Validation\Rules\IsArrayValidator;

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
            'preference' => [
                'name'       => 'preference',
                'required'   => true,
                'validators' => [
                    new IsArrayValidator(["name" => "preference"])
                ]
            ],
            'mongoId' => [
                'name'       => 'mongoId',
                'required'   => false,
                'validators' => []
            ],
        ];
    }
}