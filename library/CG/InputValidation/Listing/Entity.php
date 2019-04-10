<?php
namespace CG\InputValidation\Listing;

use CG\Validation\Rules\DecimalValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\ArrayOfPositiveIntegersValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\Date;
use Zend\Validator\GreaterThan;
use Zend\Validator\StringLength;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return [
            'id' => [
                'name'       => 'id',
                'required'   => false,
                'validators' => []
            ],
            'organisationUnitId' => [
                'name'       => 'organisationUnitId',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ],
            'productIds' => [
                'name'       => 'productIds',
                'required'   => false,
                'validators' => [
                    new ArrayOfPositiveIntegersValidator(new IntegerValidator(['productIds' => 'productId']))
                ]
            ],
            'externalId' => [
                'name' => 'externalId',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'channel' => [
                'name'       => 'channel',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'status' => [
                'name'       => 'status',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'name' => [
                'name'       => 'status',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'description' => [
                'name'       => 'description',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'price' => [
                'name'       => 'price',
                'required'   => false,
                'validators' => [new DecimalValidator(['min' => 0])]
            ],
            'cost' => [
                'name'       => 'cost',
                'required'   => false,
                'validators' => [new DecimalValidator(['min' => 0])]
            ],
            'condition' => [
                'name'       => 'condition',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'accountId' => [
                'name'       => 'accountId',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'accountId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'accountId must be at least %min%'])
                ]
            ],
            'url' => [
                'name'       => 'url',
                'required'   => false,
                'validators' => [
                    new StringLength(['min' => 1, 'max' => 2000])
                ]
            ],
            'marketplace' => [
                'name'       => 'marketplace',
                'required'   => false,
                'validators' => []
            ],
            'productSkus' => [
                'name'       => 'productSkus',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'productSkus'])
                ]
            ],
            'replacedById' => [
                'name'       => 'replacedById',
                'required'   => false,
                'validators' => [
                    new IntegerValidator(['name' => 'replacedById']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'replacedById must be at least %min%'])
                ]
            ],
            'skuExternalIdMap' => [
                'name'       => 'skuExternalIdMap',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'skuExternalIdMap'])
                ]
            ],
            'lastModified' => [
                'name'       => 'lastModified',
                'required'   => false,
                'validators' => [new Date(['format' => 'Y-m-d H:i:s'])]
            ],
        ];
    }
}
