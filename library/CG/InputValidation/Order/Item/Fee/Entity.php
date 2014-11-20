<?php
namespace CG\InputValidation\Order\Item\Fee;

use CG\Validation\RulesInterface;
use Zend\Validator\StringLength;
use Zend\Validator\GreaterThan;
use CG\Validation\Rules\DecimalValidator;
use CG\Validation\Rules\IntegerValidator;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return array(
            'id' => array(
                'name'       => 'id',
                'required'   => false,
                'validators' => []
            ),
            'orderItemId' => array(
                'name'       => 'orderItemId',
                'required'   => false,
                'validators' => []
            ),
            'name' => array(
                'name'       => 'name',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ),
            'organisationUnitId' => array(
                'name'       => 'organisationUnitId',
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ),
            'amount' => array(
                'name'       => 'amount',
                'required'   => true,
                'validators' => [new DecimalValidator(['min' => 0, 'name' => 'amount'])]
            )
        );
    }
}