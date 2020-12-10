<?php
namespace CG\InputValidation\Order\Label;

use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\DecimalValidator;
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
            'channelName' => [
                'name'       => 'channelName',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'courierName' => [
                'name'       => 'courierName',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'courierService' => [
                'name'       => 'courierService',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'deliveryInstructions' => [
                'name'       => 'deliveryInstructions',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'signature' => [
                'name'       => 'signature',
                'required'   => false,
                'allow_empty' => true,
                'validators' => [new BooleanValidator(["name" => "signature"])]
            ],
            'parcels' => [
                'name'       => 'parcels',
                'required'   => false,
                'validators' => [new IsArrayValidator(["name" => "parcels"])]
            ],
            'insurance' => [
                'name'       => 'insurance',
                'required'   => false,
                'allow_empty' => true,
                'validators' => [new BooleanValidator(["name" => "insurance"])]
            ],
            'insuranceMonetary' => [
                'name'       => 'insuranceMonetary',
                'required'   => false,
                'validators' => [new DecimalValidator(['name' => 'insuranceMonetary'])]
            ],
            'mongoId' => [
                'name'       => 'mongoId',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1, 'max' => 24])]
            ],
            'costPrice' => [
                'name'       => 'costPrice',
                'required'   => false,
                'validators' => [new DecimalValidator(['name' => 'costPrice'])]
            ],
            'costCurrencyCode' => [
                'name'       => 'costCurrencyCode',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'images' => [
                'name'       => 'images',
                'required'   => false,
                'validators' => [new IsArrayValidator(["name" => "images"])]
            ],
        ];
    }
}