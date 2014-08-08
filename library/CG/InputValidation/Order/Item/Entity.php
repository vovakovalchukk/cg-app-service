<?php
namespace CG\InputValidation\Order\Item;

use CG\Validation\RulesInterface;
use Zend\Di\Di;
use Zend\Validator\Date;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\DecimalValidator;
use Zend\Validator\StringLength;
use Zend\Validator\GreaterThan;
use CG\Stdlib\DateTime as StdlibDateTime;

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
            'externalId' => [
                'name'       => 'externalId',
                'required'   => false,
                'validators' => []
            ],
            'orderId' => array(
                'name'       => 'orderId',
                'required'   => false,
                'validators' => array(
                )
            ),
            'accountId' => array(
                'name'       => 'accountId',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(IntegerValidator::class, ['name' => 'userId']),
                    $this->getDi()->newInstance(GreaterThan::class, ['options' => ['min' => 1, 'inclusive' => true]])
                                  ->setMessages(['notGreaterThanInclusive' => 'accountId must be at least %min%'])
                )
            ),
            'itemName' => array(
                'name'       => 'itemName',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                )
            ),
            'individualItemPrice' => array(
                'name'       => 'individualItemPrice',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(DecimalValidator::class, ['name' => 'individualItemPrice', 'min' => 0]),
                )
            ),
            'itemQuantity' => array(
                'name'       => 'itemQuantity',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(IntegerValidator::class, ['name' => 'itemQuantity']),
                    $this->getDi()->newInstance(GreaterThan::class, ['options' => ['min' => 0, 'inclusive' => true]])
                                  ->setMessages(['notGreaterThanInclusive' => 'itemQuantity must be at least %min%'])
                )
            ),
            'itemSku' => array(
                'name'       => 'itemSku',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
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
            'itemTaxPercentage' => array(
                'name'       => 'itemTaxPercentage',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(DecimalValidator::class, ['name' => 'itemTaxPercentage', 'min' => 0]),
                )
            ),
            'individualItemDiscountPrice' => array(
                'name'       => 'individualItemDiscountPrice',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(DecimalValidator::class, ['name' => 'individualItemDiscountPrice', 'min' => 0]),
                )
            ),
            'itemVariationAttribute' => array(
                'name'       => 'itemVariationAttribute',
                'required'   => false,
                'validators' => array(
                )
            ),
            'status' => array(
                'name'       => 'status',
                'validators' => array(
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                )
            ),
            'purchaseDate' => array(
                'name'       => 'purchaseDate',
                'validators' => array(
                    $this->getDi()->newInstance(Date::class, array('options' => array('format' => StdlibDateTime::FORMAT)))
                )
            ),
        );
    }
}
