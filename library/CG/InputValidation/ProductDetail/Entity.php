<?php
namespace CG\InputValidation\ProductDetail;

use CG\Ebay\Site\EanDoesNotApplyMap;
use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\DecimalValidator;
use CG\Validation\Rules\InArrayValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\RulesInterface;
use CG\Validation\ValidatorChain;
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
                'validators' => [
                    new IntegerValidator(['name' => 'id']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'id must be at least %min%'])
                ]
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
            'weight' => [
                'name'       => 'weight',
                'required'   => false,
                'validators' => [new DecimalValidator(['name' => 'weight'])]
            ],
            'width' => [
                'name'       => 'width',
                'required'   => false,
                'validators' => [new DecimalValidator(['name' => 'width'])]
            ],
            'height' => [
                'name'       => 'height',
                'required'   => false,
                'validators' => [new DecimalValidator(['name' => 'height'])]
            ],
            'length' => [
                'name'       => 'length',
                'required'   => false,
                'validators' => [new DecimalValidator(['name' => 'length'])]
            ],
            'description' => [
                'name'       => 'description',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'ean' => [
                'name'       => 'ean',
                'required'   => false,
                'validators' => [$this->getBarcodeChainValidator(1, 13, 'ean')]
            ],
            'brand' => [
                'name'       => 'brand',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'mpn' => [
                'name'       => 'mpn',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'asin' => [
                'name'       => 'asin',
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
            'categoryTemplateIds' => [
                'name' => 'categoryTemplateIds',
                'required' => false,
                'validators' => [new ArrayOfIntegersValidator(new IntegerValidator(), 'categoryTemplateIds')]
            ],
            'upc' => [
                'name' => 'upc',
                'required' => false,
                'validators' => [$this->getBarcodeChainValidator(12, 13, 'upc')]
            ],
            'isbn' => [
                'name' => 'isbn',
                'required' => false,
                'validators' => [$this->getBarcodeChainValidator(10, 13, 'isbn')]
            ],
            'gtin' => [
                'name' => 'gtin',
                'required' => false,
                'validators' => [new StringLength(['min' => 8, 'max' => 14])]
            ],
            'barcodeNotApplicable' => [
                'name' => 'barcodeNotApplicable',
                'required' => false,
                'validators' => [new BooleanValidator(['name' => 'barcodeNotApplicable'])]
            ],
            'supplierId' => [
                'name'       => 'supplierId',
                'required'   => false,
                'validators' => [
                    new IntegerValidator(['name' => 'supplierId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'supplierId must be at least %min%'])
                ]
            ],
            'hsTariffNumber' => [
                'name' => 'hsTariffNumber',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
        ];
    }

    protected function getBarcodeChainValidator($min, $max, $name)
    {
        return (new ValidatorChain(
            [
                new InArrayValidator($name, EanDoesNotApplyMap::getAllowedDoesNotApplyStrings()),
                new StringLength(['min' => $min, 'max' => $max])
            ],
            true
        ))->setMessage($name . ' must be between ' . $min . ' and ' . $max .' characters');
    }

}