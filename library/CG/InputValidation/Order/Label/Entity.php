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
            'image' => [
                'name'       => 'image',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'externalId' => [
                'name'       => 'externalId',
                'required'   => false,
                'validators' => []
            ],
            'shippingAccountId' => [
                'name'       => 'shippingAccountId',
                'required'   => false,
                'validators' => [
                    new IntegerValidator(['name' => 'shippingAccountId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'shippingAccountId must be at least %min%'])
                ]
            ],
            'shippingServiceCode' => [
                'name'       => 'shippingServiceCode',
                'required'   => false,
                'validators' => []
            ],
            'courierName' => [
                'name'       => 'courierName',
                'required'   => false,
                'validators' => []
            ],
            'courierService' => [
                'name'       => 'courierService',
                'required'   => false,
                'validators' => []
            ],
            'deliveryInstructions' => [
                'name'       => 'deliveryInstructions',
                'required'   => false,
                'validators' => []
            ],
            'signature' => [
                'name'       => 'signature',
                'required'   => false,
                'validators' => []
            ],
            'parcels' => [
                'name'       => 'parcels',
                'required'   => false,
                'validators' => []
            ],
            'insurance' => [
                'name'       => 'insurance',
                'required'   => false,
                'validators' => []
            ],
            'insuranceMonetary' => [
                'name'       => 'insuranceMonetary',
                'required'   => false,
                'validators' => []
            ],
        ];
    }
}