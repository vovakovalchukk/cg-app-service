<?php
namespace CG\InputValidation\Settings\Order;

use CG\Validation\Rules\IsArrayValidator;
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
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'id']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'id must be at least %min%'])
                ]
            ],
            'autoArchiveTimeframe' => [
                'name' => 'autoArchiveTimeframe',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
        ];
    }
}
