<?php
namespace CG\InputValidation\Listing;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\ArrayOfPositiveIntegersValidator;
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
            'productIds' => [
                'name'       => 'productIds',
                'required'   => true,
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
        ];
    }
}