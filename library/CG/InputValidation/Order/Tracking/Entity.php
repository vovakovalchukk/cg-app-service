<?php
namespace CG\InputValidation\Order\Tracking;

use CG\Validation\RulesInterface;
use Zend\Validator\Date;
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
                'required'   => false,
                'validators' => []
            ),
            'orderId' => array(
                'name'       => 'orderId',
                'required'   => false,
                'validators' => []
            ),
            'userId' => array(
                'name'       => 'userId',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'userId']),
                    (new GreaterThan(['min' => 0, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'userId must be at least %min%'])
                ]
            ),
            'number' => array(
                'name'       => 'number',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ),
            'carrier' => array(
                'name'       => 'carrier',
                'required'   => true,
                'validators' => []
            ),
            'organisationUnitId' => array(
                'name'       => 'organisationUnitId',
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ),
            'timestamp' => array(
                'name'       => 'timestamp',
                'required'   => true,
                'validators' => [new Date(['format' => 'Y-m-d H:i:s'])]
            ),
            'status' => [
                'name'       => 'status',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'packageNumber' => [
                'name'       => 'packageNumber',
                'required'   => false,
                'validators' => [new IntegerValidator(['name' => 'packageNumber'])]
            ],
        );
    }
}