<?php
namespace CG\InputValidation\Order\Label;

use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\RulesInterface;
use Zend\Validator\Date;

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
            'organisationUnitId' => [
                'name' => 'organisationUnitId',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                ]
            ],
            'orderId' => [
                'name'       => 'orderId',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(["name" => "orderId"])
                ],
            ],
            'status' => [
                'name'       => 'status',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(["name" => "status"])
                ],
            ],
            'shippingAccountId' => [
                'name' => 'shippingAccountId',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'shippingAccountId')
                ]
            ],
            'createdFrom' => [
                'name'       => 'createdFrom',
                'required'   => false,
                'validators' => [new Date(['format' => StdlibDateTime::FORMAT])]
            ],
            'createdTo' => [
                'name'       => 'createdTo',
                'required'   => false,
                'validators' => [new Date(['format' => StdlibDateTime::FORMAT])]
            ],
            'shippingServiceCode' => [
                'name' => 'shippingServiceCode',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "shippingServiceCode"])
                ]
            ],
        ];
    }
}
