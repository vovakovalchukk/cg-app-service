<?php
namespace CG\InputValidation\PurchaseOrderItem;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\Rules\ValidatorTrait;
use CG\Validation\RulesInterface;
use Zend\Di\Di;

class Filter implements RulesInterface
{
    use ValidatorTrait;
    use PaginationTrait;

    public function __construct(Di $di)
    {
        $this->setDi($di);
    }

    public function getRules()
    {
        $rules = [
            'id' => [
                'name'       => 'id',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'id')
                ]
            ],
            'organisationUnitId' => [
                'name'       => 'organisationUnitId',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                ]
            ],
            'purchaseOrderId' => [
                'name'       => 'purchaseOrderId',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'purchaseOrderId')
                ]
            ],
            'sku' => [
                'name'       => 'sku',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'sku'])
                ]
            ],
        ];

        return array_merge($this->getPaginationValidation(), $rules);
    }
}
