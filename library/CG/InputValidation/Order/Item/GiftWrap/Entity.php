<?php
namespace CG\InputValidation\Order\Item\GiftWrap;

use CG\Validation\RulesInterface;
use Zend\Di\Di;
use Zend\Validator\StringLength;
use Zend\Validator\GreaterThan;
use CG\Validation\Rules\DecimalValidator;
use CG\Validation\Rules\IntegerValidator;

class Entity implements RulesInterface
{
    protected $di;

    public function __construct(Di $di)
    {
        $this->setDi($di);
    }

    protected function getDi()
    {
        return $this->di;
    }

    protected function setDi(Di $di)
    {
        $this->di = $di;
    }

    public function getRules()
    {
        return array(
            'id' => array(
                'name'       => 'id',
                'required'   => false,
                'validators' => array(
                )
            ),
            'orderItemId' => array(
                'name'       => 'orderItemId',
                'required'   => false,
                'validators' => array(
                )
            ),
            'giftWrapType' => array(
                'name'       => 'giftWrapType',
                'required'   => false,
                'validators' => array(
                )
            ),
            'giftWrapMessage' => array(
                'name'       => 'giftWrapMessage',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                )
            ),
            'giftWrapPrice' => array(
                'name'       => 'giftWrapPrice',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(DecimalValidator::class, ['name' => 'giftWrapPrice', 'min' => 0])
                )
            ),
            'organisationUnitId' => array(
                'name'       => 'organisationUnitId',
                'validators' => array(
                    $this->getDi()->newInstance(IntegerValidator::class, ['name' => 'organisationUnitId']),
                    $this->getDi()->newInstance(GreaterThan::class, ['options' => ['min' => 1, 'inclusive' => true]])
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                )
            ),
            'giftWrapTaxPercentage' => array(
                'name'       => 'giftWrapTaxPercentage',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(DecimalValidator::class, ['name' => 'giftWrapTaxPercentage', 'min' => 0])
                )
            )
        );
    }
}