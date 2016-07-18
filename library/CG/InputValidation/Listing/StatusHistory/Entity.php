<?php
namespace CG\InputValidation\Listing\StatusHistory;

use CG\Stdlib\DateTime;
use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
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
                'name' => 'id',
                'required' => false,
                'validators' => [],
            ],
            'listingId' => [
                'name' => 'listingId',
                'required' => true,
                'validators' => [
                    new IntegerValidator(['name' => 'listingId']),
                    (new GreaterThan(['min' => 0]))->setMessages([GreaterThan::NOT_GREATER_INCLUSIVE => 'listingId must be at least %min%']),
                ],
            ],
            'timestamp' => [
                'name' => 'timestamp',
                'required' => true,
                'validators' => [
                    new Date(['format' => DateTime::FORMAT]),
                ],
            ],
            'status' => [
                'name' => 'status',
                'required' => true,
                'validators' => [
                    new StringLength(['min' => 1]),
                ],
            ],
            'message' => [
                'name' => 'message',
                'required' => true,
                'validators' => [
                    new StringLength(['min' => 1]),
                ],
            ],
            'code' => [
                'name' => 'code',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(['name' => 'code'])),
                ],
            ],
        ];
    }
} 
