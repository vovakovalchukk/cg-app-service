<?php
namespace CG\InputValidation\Order\Order;

use CG\Validation\RulesInterface;
use Zend\Di\Di;
use Zend\Validator\Date;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\DecimalValidator;
use Zend\Validator\StringLength;
use Zend\Validator\GreaterThan;
use Zend\Validator\InArray;
use CG\Constant\CurrencyCode;
use CG\Constant\CountryCode;

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
            'accountId' => array(
                'name'       => 'accountId',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(IntegerValidator::class, ['name' => 'accountId']),
                    $this->getDi()->newInstance(GreaterThan::class, ['options' => ['min' => 1, 'inclusive' => true]])
                                  ->setMessages(['notGreaterThanInclusive' => 'accountId must be at least %min%'])
                )
            ),
            'externalId' => array(
                'name'       => 'externalId',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                )
            ),
            'channel' => array(
                'name'       => 'note',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                )
            ),
            'organisationUnitId' => array(
                'name'       => 'organisationUnitId',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(IntegerValidator::class, ['name' => 'organisationUnitId']),
                    $this->getDi()->newInstance(GreaterThan::class, ['options' => ['min' => 1, 'inclusive' => true]])
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                )
            ),
            'total' => array(
                'name'       => 'total',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(DecimalValidator::class, ['name' => 'total']),
                )
            ),
            'totalDiscount' => array(
                'name'       => 'totalDiscount',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(DecimalValidator::class, ['name' => 'totalDiscount']),
                )
            ),
            'status' => array(
                'name'       => 'status',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                )
            ),
            'shippingPrice' => array(
                'name'       => 'shippingPrice',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(DecimalValidator::class, ['name' => 'shippingPrice']),
                )
            ),
            'shippingMethod' => array(
                'name'       => 'shippingMethod',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                )
            ),
            'currencyCode' => array(
                'name'       => 'currencyCode',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->get(InArray::class)
                         ->setHaystack(CurrencyCode::getCurrencyCodes())
                )
            ),
            'buyerMessage' => array(
                'name'       => 'buyerMessage',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                )
            ),
            'purchaseDate' => array(
                'name'       => 'purchaseDate',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(Date::class, array('options' => array('format' => "Y-m-d H:i:s")))
                )
            ),
            'paymentDate' => array(
                'name'       => 'paymentDate',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(Date::class, array('options' => array('format' => "Y-m-d H:i:s")))
                )
            ),
            'printedDate' => array(
                'name'       => 'printedDate',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(Date::class, array('options' => array('format' => "Y-m-d H:i:s")))
                )
            ),
            'dispatchDate' => array(
                'name'       => 'dispatchDate',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(Date::class, array('options' => array('format' => "Y-m-d H:i:s")))
                )
            ),
            'custom' => array(
                'name'       => 'custom',
                'required'   => false,
                'validators' => array(
                )
            ),
            'billingAddressCompanyName'  => array(
                'name'       => 'billingAddressCompanyName',
                'required'   => false,
                'validators' => array(
                )
            ),
            'billingAddressFullName'  => array(
                'name'       => 'billingAddressFullName',
                'required'   => false,
                'validators' => array(
                )
            ),
            'billingAddress1'  => array(
                'name'       => 'billingAddress1',
                'required'   => false,
                'validators' => array(
                )
            ),
            'billingAddress2'  => array(
                'name'       => 'billingAddress2',
                'required'   => false,
                'validators' => array(
                )
            ),
            'billingAddress3'  => array(
                'name'       => 'billingAddress3',
                'required'   => false,
                'validators' => array(
                )
            ),
            'billingAddressCity'  => array(
                'name'       => 'billingAddressCity',
                'required'   => false,
                'validators' => array(
                )
            ),
            'billingAddressCounty'  => array(
                'name'       => 'billingAddressCounty',
                'required'   => false,
                'validators' => array(
                )
            ),
            'billingAddressCountry'  => array(
                'name'       => 'billingAddressCountry',
                'required'   => false,
                'validators' => array(
                )
            ),
            'billingAddressPostcode'  => array(
                'name'       => 'billingAddressPostcode',
                'required'   => false,
                'validators' => array(
                )
            ),
            'billingAddressCountryCode'  => array(
                'name'       => 'billingAddressCountryCode',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(InArray::class)
                        ->setHaystack(CountryCode::getCountryCodes())
                )
            ),
            'billingEmailAddress'  => array(
                'name'       => 'billingEmailAddress',
                'required'   => false,
                'validators' => array(
                )
            ),
            'billingPhoneNumber'  => array(
                'name'       => 'billingPhoneNumber',
                'required'   => false,
                'validators' => array(
                )
            ),
            'shippingAddressCompanyName'  => array(
                'name'       => 'shippingAddressCompanyName',
                'required'   => false,
                'validators' => array(
                )
            ),
            'shippingAddressFullName'  => array(
                'name'       => 'shippingAddressFullName',
                'required'   => false,
                'validators' => array(
                )
            ),
            'shippingAddress1'  => array(
                'name'       => 'shippingAddress1',
                'required'   => false,
                'validators' => array(
                )
            ),
            'shippingAddress2'  => array(
                'name'       => 'shippingAddress2',
                'required'   => false,
                'validators' => array(
                )
            ),
            'shippingAddress3'  => array(
                'name'       => 'shippingAddress3',
                'required'   => false,
                'validators' => array(
                )
            ),
            'shippingAddressCity'  => array(
                'name'       => 'shippingAddressCity',
                'required'   => false,
                'validators' => array(
                )
            ),
            'shippingAddressCounty'  => array(
                'name'       => 'shippingAddressCounty',
                'required'   => false,
                'validators' => array(
                )
            ),
            'shippingAddressCountry'  => array(
                'name'       => 'shippingAddressCountry',
                'required'   => false,
                'validators' => array(
                )
            ),
            'shippingAddressPostcode'  => array(
                'name'       => 'shippingAddressPostcode',
                'required'   => false,
                'validators' => array(
                )
            ),
            'shippingAddressCountryCode'  => array(
                'name'       => 'shippingAddressCountryCode',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(InArray::class)
                                  ->setHaystack(CountryCode::getCountryCodes())
                )
            ),
            'shippingEmailAddress'  => array(
                'name'       => 'shippingEmailAddress',
                'required'   => false,
                'validators' => array(
                )
            ),
            'shippingPhoneNumber'  => array(
                'name'       => 'shippingPhoneNumber',
                'required'   => false,
                'validators' => array(
                )
            ),
            'batch' => array(
                'name'       => 'batch',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(IntegerValidator::class, ['name' => 'batch']),
                    $this->getDi()->newInstance(GreaterThan::class, ['options' => ['min' => 1, 'inclusive' => true]])
                        ->setMessages(['notGreaterThanInclusive' => 'batch must be at least %min%'])
                )
            )
        );
    }
}