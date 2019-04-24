<?php
namespace CG\InputValidation\Listing\Unimported;

use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;
use Zend\Validator\StringLength;
use Zend\Validator\Date;
use Zend\Validator\Uri;

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
            'accountId' => [
                'name'       => 'accountId',
                'required'   => true,
                'validators' => [new IntegerValidator(['name' => 'accountId'])]
            ],
            'externalId' => [
                'name'       => 'externalId',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'sku' => [
                'name'       => 'sku',
                'required'   => false,
                'validators' => []
            ],
            'title' => [
                'name'       => 'title',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'url' => [
                'name'       => 'url',
                'required'   => true,
                'validators' => [new Uri(['name' => 'url'])]
            ],
            'imageId' => [
                'name'       => 'imageId',
                'required'   => false,
                'validators' => [new IntegerValidator(['name' => 'imageId'])]
            ],
            'createdDate' => [
                'name'       => 'createdDate',
                'required'   => true,
                'validators' => [new Date(['format' => 'Y-m-d H:i:s'])]
            ],
            'status' => [
                'name'       => 'status',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'variationCount' => [
                'name'       => 'variationCount',
                'required'   => true,
                'validators' => [new IntegerValidator(['name' => 'variationCount'])]
            ],
            'hidden' => [
                'name'       => 'hidden',
                'required'   => false,
                'allow_empty' => true,
                'validators' => [new BooleanValidator(['name' => 'hidden'])]
            ],
            'channel' => [
                'name'       => 'channel',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'variationSkus'  => [
                'name' => 'variationSkus',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "variationSkus"])
                ]
            ],
            'marketplace' => [
                'name'       => 'marketplace',
                'required'   => false,
                'validators' => []
            ],
            'lastModified' => [
                'name'       => 'lastModified',
                'required'   => false,
                'validators' => [new Date(['format' => 'Y-m-d H:i:s'])]
            ],
        ];
    }
}
