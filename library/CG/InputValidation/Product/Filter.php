<?php
namespace CG\InputValidation\Product;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use CG\Validation\ValidatorChain;
use Zend\Validator\Between;
use Zend\Validator\Identical;
use Zend\Validator\StringLength;
use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\PaginationTrait;

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
                    'validators' => [
                        new BooleanValidator(['name' => 'embedVariationsAsLinks'])
                    ]
                ],
                'replaceVariationWithParent' => [
                    'name'       => 'replaceVariationWithParent',
                    'required'   => false,
                    'validators' => [
                        new BooleanValidator(['name' => 'replaceVariationWithParent'])
                    ]
                ],
                'cgCreationDate' => [
                    'name'       => 'cgCreationDate',
                    'required'   => false,
                    'validators' => [new StringLength(['min' => 1])]
                ],
                'stockMode' => [
                    'name'       => 'stockMode',
                    'required'   => false,
                    'validators' => [
                        new IsArrayValidator(['name' => 'stockMode'])
                    ]
                ],
                'type' => [
                    'name'       => 'type',
                    'required'   => false,
                    'validators' => [
                        new IsArrayValidator(['type' => 'stockMode'])
                    ]
                ],
            ]
        );
    }
}
