<?php
namespace CG\InputValidation\Amazon\Carrier;

use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\DecimalValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\StringLength;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return [
            'id' => [
                'name' => 'id',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])],
            ],
            'carrier' => [
                'name' => 'carrier',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])],
            ],
            'service' => [
                'name' => 'service',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])],
            ],
            'currencyCode' => [
                'name' => 'currencyCode',
                'required' => true,
                'validators' => [new StringLength(['min' => 1, 'max' => 3])],
            ],
            'rate' => [
                'name' => 'rate',
                'required' => true,
                'validators' => [new DecimalValidator(['name' => 'rate'])],
            ],
            'deliveryExperience' => [
                'name' => 'deliveryExperience',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])],
            ],
            'carrierWillPickUp' => [
                'name' => 'carrierWillPickUp',
                'required' => true,
                'validators' => [new BooleanValidator(['name' => 'carrierWillPickUp'])],
            ],
        ];
    }
} 