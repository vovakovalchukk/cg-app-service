<?php

namespace CG\InputValidation\Settings\Alias\Rule;

use CG\Validation\Rules\ArrayOfPositiveIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\RulesInterface;

class Filter implements RulesInterface
{
    use PaginationTrait;

    public function getRules(): array
    {
        return [
            'limit' => $this->getLimitValidation(),
            'page' => $this->getPageValidation(),
            'id' => [
                'name' => 'id',
                'required' => false,
                'validators' => [
                    new ArrayOfPositiveIntegersValidator(new IntegerValidator(), 'id')
                ]
            ],
            'shippingAliasId' => [
                'name' => 'shippingAliasId',
                'required' => false,
                'validators' => [
                    new ArrayOfPositiveIntegersValidator(new IntegerValidator(), 'shippingAliasId')
                ],
            ],
        ];
    }
}