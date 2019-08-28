<?php
namespace CG\InputValidation\Listing;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
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
            'productId' => [
                'name'       => 'productId',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'productId')
                ]
            ],
            'externalId' => [
                'name' => 'externalId',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "externalId"])
                ]
            ],
            'channel' => [
                'name' => 'channel',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "channel"])
                ]
            ],
            'status' => [
                'name' => 'status',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "status"])
                ]
            ],
            'accountId' => [
                'name' => 'accountId',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'accountId')
                ]
            ],
            'statusExclude' => [
                'name' => 'statusExclude',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "statusExclude"])
                ]
            ],
            'marketplace' => [
                'name' => 'marketplace',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'marketplace'])
                ]
            ],
            'url' => [
                'name' => 'url',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'url'])
                ]
            ],
        ];
    }
}
