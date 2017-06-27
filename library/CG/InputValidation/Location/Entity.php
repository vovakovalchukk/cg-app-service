<?php
namespace CG\InputValidation\Location;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\BooleanValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;
use Zend\Validator\StringLength;
use Zend\Di\Di;

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
            'organisationUnitId' => [
                'name'       => 'organisationUnitId',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ],
            'type' => [
                'name'       => 'type',
                'required'   => true,
                'validators' => [
                    new StringLength(['min' => 1])
                ]
            ],
            'includeStockOnAllChannels' => [
                'name'       => 'includeStockOnAllChannels',
                'required'   => true,
                'validators' => [
                    new BooleanValidator(['name' => 'includeStockOnAllChannels'])
                ]
            ]
        ];
    }
}