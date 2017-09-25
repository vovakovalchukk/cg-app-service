<?php
namespace CG\InputValidation\PurchaseOrderItem;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use CG\Validation\Rules\ValidatorTrait;
use Zend\Di\Di;
use Zend\Validator\GreaterThan;

class Entity implements RulesInterface
{
    use ValidatorTrait;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function getRules()
    {
        return [
            'id' => [
                'name'       => 'id',
                'required'   => false,
                'validators' => [
                    new IntegerValidator(['name' => 'id']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'id must be at least %min%'])
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
            'purchaseOrderId' => [
                'name'       => 'purchaseOrderId',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'purchaseOrderId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'purchaseOrderId must be at least %min%'])
                ]
            ],
            'sku' => [
                'name'       => 'sku',
                'required'   => true,
                'validators' => []
            ],
            'quantity' => [
                'name'       => 'quantity',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'quantity']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'quantity must be at least %min%'])
                ]
            ]
        ];
    }
}
