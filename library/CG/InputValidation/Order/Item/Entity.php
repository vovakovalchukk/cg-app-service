<?php
namespace CG\InputValidation\Order\Item;

use CG\Validation\RulesInterface;
use Zend\Validator\Date;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\DecimalValidator;
use Zend\Validator\StringLength;
use Zend\Validator\GreaterThan;
use CG\Stdlib\DateTime as StdlibDateTime;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return [
            'id' => array(
                'name'       => 'id',
                'required'   => false,
                'validators' => []
            ),
            'externalId' => [
                'name'       => 'externalId',
                'required'   => false,
                'validators' => []
            ],
            'orderId' => array(
                'name'       => 'orderId',
                'required'   => false,
                'validators' => []
            ),
            'accountId' => array(
                'name'       => 'accountId',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'accountId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'accountId must be at least %min%'])
                ]
            ),
            'itemName' => array(
                'name'       => 'itemName',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ),
            'individualItemPrice' => array(
                'name'       => 'individualItemPrice',
                'required'   => true,
                'validators' => [new DecimalValidator(['min' => 0, 'name' => 'individualItemPrice'])]
            ),
            'itemQuantity' => array(
                'name'       => 'itemQuantity',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'itemQuantity']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'itemQuantity must be at least %min%'])
                ]
            ),
            'itemSku' => array(
                'name'       => 'itemSku',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ),
            'organisationUnitId' => array(
                'name'       => 'organisationUnitId',
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ),
            'itemTaxPercentage' => array(
                'name'       => 'itemTaxPercentage',
                'required'   => true,
                'validators' => [new DecimalValidator(['min' => 0, 'name' => 'itemTaxPercentage'])]
            ),
            'individualItemDiscountPrice' => array(
                'name'       => 'individualItemDiscountPrice',
                'required'   => true,
                'validators' => [new DecimalValidator(['min' => 0, 'name' => 'individualItemDiscountPrice'])]
            ),
            'itemVariationAttribute' => [
                'name'       => 'itemVariationAttribute',
                'required'   => false,
                'validators' => []
            ],
            'status' => [
                'name'       => 'status',
                'validators' => [new StringLength(['min' => 1])]
            ],
            'purchaseDate' => [
                'name'       => 'purchaseDate',
                'validators' => [new Date(['format' => 'Y-m-d H:i:s'])]
            ],
            'url' => [
                'name'       => 'url',
                'required'   => false,
                'validators' => [
                    new StringLength(['min' => 1, 'max' => 2000])
                ]
            ]
        ];
    }
}
