<?php
namespace CG\InputValidation\CategoryExternal;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;
use Zend\Validator\StringLength;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return [
            'categoryId' => [
                'name'       => 'categoryId',
                'required'   => false,
                'validators' => [
                    new IntegerValidator(['name' => 'categoryId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'categoryId must be at least %min%'])
                ]
            ],
            'channel' => [
                'name'       => 'channel',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'externalData' => [
                'name' => 'externalData',
                'required'   => true,
                'validators' => [
                    new IsArrayValidator(['name' => 'externalData'])
                ]
            ]
        ];
    }
}
