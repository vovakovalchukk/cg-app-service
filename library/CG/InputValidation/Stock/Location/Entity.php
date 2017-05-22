<?php
namespace CG\InputValidation\Stock\Location;

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
                'required'   => false,
                'validators' => []
            ],
            'locationId' => [
                'name'       => 'locationId',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'locationId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'locationId must be at least %min%'])
                ]
            ],
            'stockId' => [
                'name'       => 'stockId',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'stockId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'stockId must be at least %min%'])
                ]
            ],
            'organisationUnitId' => [
                'name'       => 'organisationUnitId',
                'required'   => true,
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
            'onHand' => [
                'name'       => 'onHand',
                'required'   => true,
                'validators' => [new IntegerValidator(['name' => 'onHand'])]
            ],
            'allocated' => [
                'name'       => 'allocated',
                'required'   => true,
                'validators' => [new IntegerValidator(['name' => 'allocated'])]
            ],
        ];
    }
}
