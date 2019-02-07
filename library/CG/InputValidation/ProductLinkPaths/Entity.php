<?php
namespace CG\InputValidation\ProductLinkPaths;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;
use Zend\Validator\Regex;
use Zend\Validator\StringLength;
use CG\Validation\Rules\IsArrayValidator;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return [
            'id' => [
                'name' => 'id',
                'required' => true,
                'validators' => [new Regex('/^\d+-.+$/')]
            ],
            'organisationUnitId' => [
                'name' => 'organisationUnitId',
                'required' => true,
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ],
            'sku' => [
                'name' => 'sku',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'paths' => [
                'name' => 'paths',
                'required' => false,
                'validators' => [new IsArrayValidator(['name' => 'paths'])]
            ],
        ];
    }
}