<?php
namespace CG\InputValidation\Product;

use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\DecimalValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
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
            'sku' => [
                'name' => 'sku',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'name' => [
                'name'       => 'name',
                'required'   => false,
                'validators' => []
            ],
            'parentProductId' => [
                'name'       => 'parentProductId',
                'required'   => true,
                'validators' => []
            ],
            'attributeNames'  => [
                'name' => 'attributeNames',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "attributeNames"])
                ]
            ],
            'attributeValues'  => [
                'name' => 'attributeValues',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "attributeValues"])
                ]
            ],
            'deleted' => [
                'name'       => 'deleted',
                'required'   => false,
                'allow_empty' => true,
                'validators' => [new BooleanValidator(['name' => 'deleted'])]
            ],
            'images'  => [
                'name' => 'images',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'images'])
                ]
            ],
            'listingImages'  => [
                'name' => 'listingImages',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'listingImages'])
                ]
            ],
            'taxRateIds' => [
                'name' => 'taxRateIds',
                'required' => false,
                'validators' => [new IsArrayValidator(['name' => 'taxRateIds'])]
            ],
            'cgCreationDate' => [
                'name'       => 'cgCreationDate',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'pickingLocations' => [
                'name' => 'pickingLocations',
                'required' => false,
                'validators' => [new IsArrayValidator(['name' => 'pickingLocations'])]
            ],
        ];
    }
}
