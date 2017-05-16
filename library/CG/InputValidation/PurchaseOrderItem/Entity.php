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
                'validators' => []
            ],
            'SKU' => [
                'name'       => 'SKU',
                'required'   => true,
                'validators' => [
                    new IsArrayValidator(['name' => 'SKU'])
                ]
            ],
            'quantity' => [
                'name'       => 'quantity',
                'required'   => true,
                'validators' => []
            ]
        ];
    }
}
