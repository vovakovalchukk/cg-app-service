<?php
namespace CG\InputValidation\Order\Archive;

use CG\Validation\RulesInterface;
use CG\Validation\Rules\BooleanValidator;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return array(
            'orderId' => array(
                'name'       => 'orderId',
                'required'   => false,
                'validators' => []
            ),
            'archived' => array(
                'name'       => 'archived',
                'required'   => false,
                'allow_empty' => true,
                'continue_if_empty' => true,
                'validators' => [new BooleanValidator(['name' => 'archived'])]
            )
        );
    }
}