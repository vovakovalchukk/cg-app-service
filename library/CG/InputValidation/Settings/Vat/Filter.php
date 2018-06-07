<?php
namespace CG\InputValidation\Settings\Vat;

use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\RulesInterface;
use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;

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
            'chargeVat' => [
                'name' => 'chargeVat',
                'required' => false,
                'validators' => [
                    new BooleanValidator()
                ]
            ]
        ];
    }
}