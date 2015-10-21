<?php
namespace CG\InputValidation\Settings\Invoice;

use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\Date;
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
            'autoEmail' => [
                'name'       => 'autoEmail',
                'required'   => false,
                'validators' => [new Date(['format' => 'Y-m-d H:i:s'])]
            ],
            'productImages' => [
                'name'       => 'autoEmail',
                'required'   => false,
                'validators' => [new BooleanValidator(['name' => 'active'])]
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
