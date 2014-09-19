<?php
namespace CG\InputValidation\Listing\Unimported;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\Rules\BooleanValidator;
use Zend\Validator\Date;
use Zend\Validator\StringLength;

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
            'accountId' => [
                'name' => 'accountId',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'accountId')
                ]
            ],
            'externalId' => [
                'name' => 'externalId',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "externalId"])
                ]
            ],
            'sku' => [
                'name' => 'sku',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "sku"])
                ]
            ],
            'title' => [
                'name' => 'title',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "title"])
                ]
            ],
            'url' => [
                'name' => 'url',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "url"])
                ]
            ],
            'imageId' => [
                'name' => 'imageId',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'imageId')
                ]
            ],
            'createdDateFrom' => [
                'name' => 'createdDateFrom',
                'required' => false,
                'validators' => [
                    new Date(['format' => "Y-m-d H:i:s"])
                ]
            ],
            'createdDateTo' => [
                'name' => 'createdDateTo',
                'required' => false,
                'validators' => [
                    new Date(['format' => "Y-m-d H:i:s"])
                ]
            ],
            'status' => [
                'name' => 'status',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "status"])
                ]
            ],
            'variationCount' => [
                'name' => 'variationCount',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "variationCount"])
                ]
            ],
            'hidden' => [
                'name' => 'hidden',
                'required' => false,
                'validators' => [
                    new BooleanValidator(["name" => "hidden"])
                ]
            ],
            'channel' => [
                'name' => 'channel',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "channel"])
                ]
            ],
            'searchTerm' => array(
                'name' => 'searchTerm',
                'required' => false,
                'validators' => array(
                    new StringLength(['min' => 1])
                )
            ),
        ];
    }
}