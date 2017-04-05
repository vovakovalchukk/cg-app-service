<?php
namespace CG\InputValidation\Settings\InvoiceMapping;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use Zend\Validator\GreaterThan;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\RulesInterface;

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
                ]
            ],
            'organisationUnitId' => [
                'name' => 'organisationUnitId',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                ]
            ],
            'accountId' => [
                'name' => 'accountId',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'accountId')
                ]
            ],
        ];
    }
}
