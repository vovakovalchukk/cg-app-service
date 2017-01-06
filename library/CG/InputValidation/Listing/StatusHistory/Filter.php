<?php
namespace CG\InputValidation\Listing\StatusHistory;

use CG\Validation\RulesInterface;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\BooleanValidator;

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
                    new ArrayOfIntegersValidator(new IntegerValidator(['name' => 'id']), 'id'),
                ],
            ],
            'listingId' => [
                'name' => 'listingId',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(['name' => 'listingId']), 'listingId'),
                ],
            ],
            'status' => [
                'name' => 'status',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'status']),
                ],
            ],
            'latest' => [
                'name' => 'latest',
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    new BooleanValidator(['name' => 'latest']),
                ],
            ],
        ];
    }
} 
