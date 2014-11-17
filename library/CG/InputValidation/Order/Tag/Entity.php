<?php
namespace CG\InputValidation\Order\Tag;

use CG\Validation\RulesInterface;
use Zend\Di\Di;
use CG\Validation\Rules\IntegerValidator;
use Zend\Validator\StringLength;
use Zend\Validator\GreaterThan;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return array(
            'id' => array(
                'name'       => 'id',
                'required'   => true,
                'validators' => []
            ),
            'organisationUnitId' => array(
                'name'       => 'organisationUnitId',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ),
            'tag' => array(
                'name'       => 'tag',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ),
            'orderId' => array(
                'name'      => 'orderId',
                'required'  => true,
                'validators' => [new StringLength(['min' => 1])]
            )
        );
    }
}