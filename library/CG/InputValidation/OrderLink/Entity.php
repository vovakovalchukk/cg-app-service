<?php
namespace CG\InputValidation\OrderLink;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use CG\Validation\Rules\ValidatorTrait;
use Zend\Di\Di;
use Zend\Validator\GreaterThan;

class Entity implements RulesInterface
{
    use ValidatorTrait;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

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
            'orderIds' => [
                'name' => 'orderIds',
                'required' => true,
                'validators' => [
                    new IsArrayValidator(["name" => "orderIds"])
                ]
            ],
        ];
    }
}