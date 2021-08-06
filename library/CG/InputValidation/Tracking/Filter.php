<?php
namespace CG\InputValidation\Tracking;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\Rules\ValidatorTrait;
use CG\Validation\RulesInterface;

class Filter implements RulesInterface {

    use ValidatorTrait;
    use PaginationTrait;

    public function getRules()
    {
        return [
            'limit' => $this->getLimitValidation(),
            'page' => $this->getPageValidation(),
            'id' => array(
                'name'       => 'id',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'id'])
                ]
            ),
            'orderId' => array(
                'name'       => 'orderId',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'orderId'])
                ]
            ),
            'organisationUnitId' => array(
                'name'       => 'organisationUnitId',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                ]
            ),
            'accountId' => array(
                'name'       => 'accountId',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'accountId')
                ]
            ),
            'userId' => array(
                'name'       => 'userId',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'userId')
                ]
            ),
            'carrier' => array(
                'name'       => 'carrier',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'carrier'])
                ]
            ),
            'number' => array(
                'name'       => 'number',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'number'])
                ]
            ),
            'status' => [
                'name'       => 'status',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'status'])
                ]
            ],
            'shippingService' => [
                'name' => 'shippingService',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'shippingService'])
                ]
            ],
        ];
    }
}
