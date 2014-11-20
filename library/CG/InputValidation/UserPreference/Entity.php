<?php
namespace CG\InputValidation\UserPreference;

use CG\Validation\RulesInterface;
use CG\Validation\Rules\IsArrayValidator;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return array(
            'id' => array(
                'name'       => 'id',
                'required'   => false,
                'validators' => []
            ),
            'preference' => array(
                'name'       => 'preference',
                'required'   => true,
                'validators' => [
                    new IsArrayValidator(["name" => "preference"])
                ]
            )
        );
    }
}