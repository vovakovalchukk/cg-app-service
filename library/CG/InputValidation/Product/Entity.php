<?php
namespace CG\InputValidation\Product;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;
use Zend\Validator\StringLength;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\BooleanValidator;

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
                'validators' => [new BooleanValidator(['name' => 'deleted'])]
            ],
            'imageIds'  => [
                'name' => 'imageIds',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "imageIds"])
                ]
            ],
            'taxRateId' => [
                'name' => 'taxRateId',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
        ];
    }
}