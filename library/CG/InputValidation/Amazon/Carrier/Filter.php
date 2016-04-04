<?php
namespace CG\InputValidation\Amazon\Carrier;

use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use CG\Validation\Rules\PaginationTrait;

class Filter implements RulesInterface
{
    use PaginationTrait;

    public function getRules()
    {
        return [
            'limit' => $this->getLimitValidation(),
            'page' => $this->getPageValidation(),
            'id' => [
                'name' => 'id',
                'required' => false,
                'validators' => [new IsArrayValidator(['name' => 'id'])],
            ],
            'region' => [
                'name' => 'region',
                'required' => false,
                'validators' => [new IsArrayValidator(['name' => 'region'])],
            ],
            'carrier' => [
                'name' => 'carrier',
                'required' => false,
                'validators' => [new IsArrayValidator(['name' => 'carrier'])],
            ],
            'service' => [
                'name' => 'service',
                'required' => false,
                'validators' => [new IsArrayValidator(['name' => 'service'])],
            ],
            'currencyCode' => [
                'name' => 'currencyCode',
                'required' => false,
                'validators' => [new IsArrayValidator(['name' => 'currencyCode'])],
            ],
            'deliveryExperience' => [
                'name' => 'deliveryExperience',
                'required' => false,
                'validators' => [new IsArrayValidator(['name' => 'deliveryExperience'])],
            ],
            'carrierWillPickUp' => [
                'name' => 'carrierWillPickUp',
                'required' => false,
                'validators' => [new IsArrayValidator(['name' => 'carrierWillPickUp'])],
            ],
        ];
    }
} 