<?php
namespace CG\InputValidation\Settings\Alias;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use CG\Validation\ValidatorChain;
use Zend\Validator\Between;
use Zend\Validator\Identical;
use CG\Validation\Rules\PaginationTrait;

class Filter implements RulesInterface
{
    use PaginationTrait;

    public function getRules()
    {
        return [
            'limit' => $this->getLimitValidation(),
            'page' => $this->getPageValidation(),
            'id' => [
                'name' => 'id',
                'required' => false,
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
            'accountId' => [
                'name'       => 'accountId',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'accountId')
                ]
            ],
            'shippingService' => [
                'name'       => 'shippingService',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(["name" => "shippingService"])
                ]
            ],
        ];
    }
}