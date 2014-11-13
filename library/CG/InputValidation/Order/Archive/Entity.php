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
                'validators' => array(
                )
            ),
            'archived' => array(
                'name'       => 'archived',
                'required'   => false,
                'allowEmpty' => true,
                'continueIfEmpty' => true,
                'validators' => [new BooleanValidator(['name' => 'archived'])]
            )
        );
    }
}