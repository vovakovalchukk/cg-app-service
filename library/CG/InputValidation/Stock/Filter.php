<?php
namespace CG\InputValidation\Stock;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\RulesInterface;

class Filter implements RulesInterface
{
    use PaginationTrait;

    public function getRules()
    {
        return array_merge(
            $this->getPaginationValidation(),
            [
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
                'sku' => [
                    'name' => 'sku',
                    'required' => false,
                    'validators' => [
                        new IsArrayValidator(["name" => "sku"])
                    ]
                ],
                'locationId' => [
                    'name'       => 'locationId',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'locationId')
                    ]
                ],
                'stockMode' => [
                    'name'       => 'stockMode',
                    'required'   => false,
                    'validators' => [
                        new IsArrayValidator(['name' => 'stockMode'])
                    ]
                ],
                'includePurchaseOrders' => [
                    'name' => 'includePurchaseOrders',
                    'required' => false,
                    'allow_empty' => true,
                    'validators' => [new BooleanValidator(['name' => 'includePurchaseOrders'])],
                ],
                'includePurchaseOrdersUseDefault' => [
                    'name' => 'includePurchaseOrdersUseDefault',
                    'required' => false,
                    'allow_empty' => true,
                    'validators' => [new BooleanValidator(['name' => 'includePurchaseOrdersUseDefault'])],
                ],
            ]
        );
    }
}
