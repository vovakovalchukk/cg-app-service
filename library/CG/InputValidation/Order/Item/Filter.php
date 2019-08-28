<?php
namespace CG\InputValidation\Order\Item;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\BooleanValidator;
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
            'orderIds' => [
                'name'       => 'orderIds',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'orderIds'])
                ],
            ],
            'accountId' => [
                'name'       => 'accountId',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'accountId'])
                ],
            ],
            'externalId' => [
                'name'       => 'externalId',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'externalId'])
                ],
            ],
            'itemSku' => [
                'name'       => 'itemSku',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'itemSku'])
                ],
            ],
            'status' => [
                'name'       => 'status',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'status'])
                ],
            ],
            'organisationUnitId' => [
                'name' => 'organisationUnitId',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                ]
            ],
            'cgCreationDateFrom' => [
                'name' => 'cgCreationDateFrom',
                'required' => false,
                'validators' => [
                    new Date(['format' => 'Y-m-d H:i:s'])
                ]
            ],
            'cgCreationDateTo' => [
                'name' => 'cgCreationDateTo',
                'required' => false,
                'validators' => [
                    new Date(['format' => 'Y-m-d H:i:s'])
                ]
            ],
            'purchaseDateFrom' => [
                'name' => 'purchaseDateFrom',
                'required' => false,
                'validators' => [
                    new Date(['format' => 'Y-m-d H:i:s'])
                ]
            ],
            'purchaseDateTo' => [
                'name' => 'purchaseDateTo',
                'required' => false,
                'validators' => [
                    new Date(['format' => 'Y-m-d H:i:s'])
                ]
            ],
            'lastUpdateFromChannelFrom' => [
                'name' => 'lastUpdateFromChannelFrom',
                'required' => false,
                'validators' => [
                    new Date(['format' => 'Y-m-d H:i:s'])
                ]
            ],
            'lastUpdateFromChannelTo' => [
                'name' => 'lastUpdateFromChannelTo',
                'required' => false,
                'validators' => [
                    new Date(['format' => 'Y-m-d H:i:s'])
                ]
            ],
            'externalListingId' => [
                'name'       => 'externalListingId',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'externalListingId'])
                ],
            ],
            'hasImages' => [
                'name' => 'hasImages',
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    new BooleanValidator(['name' => 'hasImages'])
                ]
            ],
            'orderArchived' => [
                'name' => 'orderArchived',
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    new BooleanValidator(['name' => 'orderArchived'])
                ]
            ],
            'dispatchable' => [
                'name' => 'dispatchable',
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    new BooleanValidator(['name' => 'dispatchable'])
                ]
            ]
        ];
    }
}
