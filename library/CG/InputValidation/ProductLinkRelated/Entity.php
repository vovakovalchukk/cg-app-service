<?php
namespace CG\InputValidation\ProductLinkRelated;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;
use Zend\Validator\Regex;
use Zend\Validator\StringLength;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return [
//            'id' => [
//                'name' => 'id',
//                'required' => true,
//                'validators' => [new Regex('/^\d+-.+$/')]
//            ],
//            'organisationUnitId' => [
//                'name' => 'organisationUnitId',
//                'required' => true,
//                'validators' => [
//                    new IntegerValidator(['name' => 'organisationUnitId']),
//                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
//                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
//                ]
//            ],
//            'sku' => [
//                'name' => 'id',
//                'required' => true,
//                'validators' => [new StringLength(['min' => 1])]
//            ],
//            'ancestors' => [
//                'name' => 'ancestors',
//                'required' => false,
//                'validators' => [new IsArrayValidator(['name' => 'ancestors'])]
//            ],
//            'descendants' => [
//                'name' => 'descendants',
//                'required' => false,
//                'validators' => [new IsArrayValidator(['name' => 'descendants'])]
//            ],
        ];
    }
}