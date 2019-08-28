<?php
namespace CG\InputValidation\Order\Item\GiftWrap;

use CG\Validation\Rules\BooleanValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\StringLength;
use Zend\Validator\GreaterThan;
use CG\Validation\Rules\DecimalValidator;
use CG\Validation\Rules\IntegerValidator;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return array(
            'id' => [
                'name'       => 'id',
                'required'   => false,
                'validators' => []
            ],
            'orderItemId' => [
                'name'       => 'orderItemId',
                'required'   => false,
                'validators' => []
            ],
            'giftWrapType' => [
                'name'       => 'giftWrapType',
                'required'   => false,
                'validators' => []
            ],
            'giftWrapMessage' => [
                'name'       => 'giftWrapMessage',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'giftWrapPrice' => [
                'name'       => 'giftWrapPrice',
                'required'   => false,
                'validators' => [new DecimalValidator(['min' => 0, 'name' => 'giftWrapPrice'])]
            ],
            'organisationUnitId' => [
                'name'       => 'organisationUnitId',
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ],
            'giftWrapTaxPercentage' => [
                'name'       => 'giftWrapTaxPercentage',
                'required'   => false,
                'validators' => [new DecimalValidator(['min' => 0, 'name' => 'giftWrapTaxPercentage'])]
            ],
            'giftWrapRedacted' => [
                'name' => 'giftWrapRedacted',
                'required' => false,
                'validators' => [new BooleanValidator(['name' => 'giftWrapRedacted'])],
            ],
        );
    }
}