<?php
namespace CG\InputValidation\Product;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\RulesInterface;
use Zend\Validator\StringLength;

class Filter implements RulesInterface
{
    use PaginationTrait;

    public function getRules()
    {
        return array_merge(
            $this->getPaginationValidation(),
            [
                'organisationUnitId' => [
                    'name'       => 'organisationUnitId',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                    ]
                ],
                'searchTerm' => [
                    'name' => 'searchTerm',
                    'required' => false,
                    'validators' => [
                        new StringLength(['min' => 1])
                    ]
                ],
                'parentProductId' => [
                    'name'       => 'parentProductId',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'parentProductId')
                    ]
                ],
                'id' => [
                    'name'       => 'id',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'id')
                    ]
                ],
                'deleted' => [
                    'name'       => 'deleted',
                    'required'   => false,
                    'allow_empty' => true,
                    'validators' => [
                        new BooleanValidator(['name' => 'deleted'])
                    ]
                ],
                'sku' => [
                    'name'       => 'sku',
                    'required'   => false,
                    'validators' => [
                        new IsArrayValidator(['name' => 'sku'])
                    ]
                ],
                'embedVariationsAsLinks' => [
                    'name'       => 'embedVariationsAsLinks',
                    'required'   => false,
                    'allow_empty' => true,
                    'validators' => [
                        new BooleanValidator(['name' => 'embedVariationsAsLinks'])
                    ]
                ],
                'replaceVariationWithParent' => [
                    'name'       => 'replaceVariationWithParent',
                    'required'   => false,
                    'allow_empty' => true,
                    'validators' => [
                        new BooleanValidator(['name' => 'replaceVariationWithParent'])
                    ]
                ],
                'returnNonEmptyNames' => [
                    'name'       => 'returnNonEmptyNames',
                    'required'   => false,
                    'allow_empty' => true,
                    'validators' => [
                        new BooleanValidator(['name' => 'returnNonEmptyNames'])
                    ]
                ],
                'cgCreationDate' => [
                    'name'       => 'cgCreationDate',
                    'required'   => false,
                    'validators' => [new StringLength(['min' => 1])]
                ],
                'type' => [
                    'name'       => 'type',
                    'required'   => false,
                    'validators' => [
                        new IsArrayValidator(['name' => 'type'])
                    ]
                ],
                'skuMatchType' => [
                    'name'       => 'skuMatchType',
                    'required'   => false,
                    'validators' => [
                        new IsArrayValidator(['name' => 'skuMatchType'])
                    ]
                ],
                'embeddedDataToReturn' => [
                    'name' => 'embeddedDataToReturn',
                    'required' => false,
                    'validators' => [
                        new IsArrayValidator(['name' => 'embeddedDataToReturn'])
                    ]
                ]
            ]
        );
    }
}
