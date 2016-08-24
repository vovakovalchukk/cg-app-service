<?php
namespace CG\InputValidation\Settings\Order;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
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
                'name' => 'id',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'id')
                ]
            ],
            'autoArchiveTimeframe' => [
                'name' => 'autoArchiveTimeframe',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'autoArchiveTimeframe'])
                ]
            ]
        ];
    }
}
