<?php
namespace CG\InputValidation\Order\Item;

use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\RulesInterface;
use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;

class Filter implements RulesInterface
{
    use PaginationTrait;

    public function getRules()
    {
        return [
            'limit' => $this->getLimitValidation(),
            'page' => $this->getPageValidation(),
            'id' => [
                'name'       => 'id',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'id')
                ],
            ],
            'orderIds' => [
                'name'       => 'orderIds',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(["name" => "orderIds"])
                ],
            ],
            'accountId' => [
                'name'       => 'accountId',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(["name" => "accountId"])
                ],
            ],
            'externalId' => [
                'name'       => 'externalId',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(["name" => "externalId"])
                ],
            ],
            'itemSku' => [
                'name'       => 'itemSku',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(["name" => "itemSku"])
                ],
            ],
            'status' => [
                'name'       => 'status',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(["name" => "status"])
                ],
            ],
            'organisationUnitId' => [
                'name' => 'organisationUnitId',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                ]
            ],
        ];
    }
}
