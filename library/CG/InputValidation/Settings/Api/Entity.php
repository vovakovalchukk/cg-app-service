<?php
namespace CG\InputValidation\Settings\Api;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;
use Zend\Validator\Uri;

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
            'stockNotificationUrl' => [
                'name'       => 'stockNotificationUrl',
                'required'   => false,
                'validators' => [
                    new Uri(['allowAbsolute' => true, 'allowRelative' => false])
                ]
            ],
            'orderNotificationUrl' => [
                'name'       => 'orderNotificationUrl',
                'required'   => false,
                'validators' => [
                    new Uri(['allowAbsolute' => true, 'allowRelative' => false])
                ]
            ],
        ];
    }
}
