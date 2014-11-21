<?php
namespace CG\InputValidation\Settings\Invoice;

use CG\Validation\RulesInterface;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use Zend\Validator\GreaterThan;
use Zend\Validator\StringLength;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return [
            'id' => [
                'name' => 'id',
                'required' => false,
                'validators' => [
                    new IntegerValidator(['name' => 'id']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'id must be at least %min%'])
                ]
            ],
            'default' => [
                'name' => 'default',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'tradingCompanies' => [
                'name' => 'tradingCompanies',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "tradingCompanies"])
                ]
            ],
        ];
    }
}