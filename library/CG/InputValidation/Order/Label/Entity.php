<?php
namespace CG\InputValidation\Order\Label;

use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Validation\Rules\IntegerValidator;
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
            'orderId' => [
                'name'       => 'orderId',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'status' => [
                'name'       => 'status',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'created' => [
                'name'       => 'created',
                'required'   => true,
                'validators' => [new Date(['format' => StdlibDateTime::FORMAT])]
            ],
            'label' => [
                'name'       => 'label',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'externalId' => [
                'name'       => 'externalId',
                'required'   => false,
                'validators' => []
            ],
        ];
    }
}