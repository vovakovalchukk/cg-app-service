<?php
namespace CG\InputValidation\Order\Order;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\RulesInterface;
use CG\Validation\ExclusionInterface;
use Zend\Di\Di;
use Zend\Validator\Between;
use Zend\Validator\Date;
use Zend\Validator\InArray;
use Zend\Validator\Identical;
use CG\Validation\Rules\BooleanValidator;
use CG\Constant\CountryCode;
use CG\Constant\CurrencyCode;
use CG\Validation\Rules\InArrayValidator;
use CG\Validation\Rules\IsArrayValidator;
use Zend\Validator\StringLength;
use CG\Validation\ValidatorChain;
use CG\Validation\Rules\DecimalValidator;
use CG\Validation\InputValidator;

class Filter implements RulesInterface, ExclusionInterface
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

    // In this instance, we want "orderFilter" to be exclusive to
    // everything else, except for "limit" and "page" so the
    // returned array is generated programmatically.
    public function getExclusions()
    {
        $rules = $this->getRules();
        unset($rules['limit']);
        unset($rules['page']);
        $ruleKeys = array_keys($rules);
        $excludeOthers = array_fill_keys($ruleKeys, true);
        $excludeFilter = array_fill_keys($ruleKeys, ['orderFilter' => true]);
        unset($excludeFilter['orderFilter']);
        return array_merge($excludeFilter, ['orderFilter' => $excludeOthers]);
    }

    public function getRules()
    {
        return array(
            'limit' => array(
                'name' => 'limit',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(
                        ValidatorChain::Class,
                        [
                            'validators' => [
                                $this->getDi()->newInstance(Between::class, array('options' => array('min' => 1)))
                                    ->setMessages(array('notBetween' => 'limit should be at least %min%')),
                                $this->getDi()->newInstance(Identical::Class, ['token' => 'all'])
                                    ->setMessages([Identical::NOT_SAME => 'limit does not equal "%token%"'])
                            ]
                        ]
                    )
                )
            ),
            'page' => array(
                'name' => 'page',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(Between::class, array('options' => array('min' => 1)))
                        ->setMessages(array('notBetween' => 'page should be at least %min%'))
                )
            ),
            'orderFilter' => array(
                'name' => 'id',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                )
            ),
            'id' => array(
                'name' => 'id',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(ArrayOfIntegersValidator::class, array("name" => "id"))
                ),
            ),
            'purchaseDateFrom' => array(
                'name' => 'purchaseDateFrom',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(Date::class, array('options' => array('format' => "Y-m-d H:i:s")))
                )
            ),
            'purchaseDateTo' => array(
                'name' => 'purchaseDateTo',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(Date::class, array('options' => array('format' => "Y-m-d H:i:s")))
                )
            ),
            'organisationUnitId' => array(
                'name' => 'organisationUnitId',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(ArrayOfIntegersValidator::class, array("name" => "organisationUnitId"))
                )
            ),
            'searchTerm' => array(
                'name' => 'searchTerm',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                )
            ),
            'status' => array(
                'name' => 'status',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, array('name' => 'status'))
                )
            ),
            'accountId' => array(
                'name' => 'accountId',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(ArrayOfIntegersValidator::class, array("name" => "accountId"))
                )
            ),
            'channel' => array(
                'name' => 'channel',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, array('name' => 'channel'))
                )
            ),
            'archived' => array(
                'name' => 'archived',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(BooleanValidator::class, ['options' => ['name' => 'archived']])
                )
            ),
            'shippingAddressCountry' => array(
                'name' => 'shippingAddressCountry',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, array('name' => 'shippingAddressCountry')),
                    $this->getDi()->newInstance(InArrayValidator::class, array('name' => 'shippingAddressCountry',
                        'haystack' => CountryCode::getCountryCodes()))
                )
            ),
            'shippingAddressCountryExclude' => array(
                'name' => 'shippingAddressCountryExclude',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, array('name' => 'shippingAddressCountryExclude')),
                    $this->getDi()->newInstance(InArrayValidator::class, array('name' => 'shippingAddressCountryExclude',
                        'haystack' => CountryCode::getCountryCodes()))
                )
            ),
            'multiLineOrder' => array(
                'name' => 'multiLineOrder',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(BooleanValidator::class, ['options' => ['name' => 'multiLineOrder']])
                )
            ),
            'multiSameItem' => array(
                'name' => 'multiSameItem',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(BooleanValidator::class, ['options' => ['name' => 'multiSameItem']])
                )
            ),
            'shippingMethod' => array(
                'name' => 'shippingMethod',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, array('name' => 'shippingMethod'))
                )
            ),
            'batch' => array(
                'name' => 'batch',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, array('name' => 'batch'))
                )
            ),
            'orderBy' => array(
                'name' => 'orderBy',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                )
            ),
            'orderDirection' => array(
                'name' => 'orderDirection',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(InArray::class)
                        ->setHaystack(array("ASC", "DESC"))
                )
            ),
            'tag' => array(
                'name' => 'tag',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, array('name' => 'tags'))
                )
            ),
            'paymentMethod' => array(
                'name' => 'paymentMethod',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, array('name' => 'paymentMethod'))
                )
            ),
            'paymentReference' => array(
                'name' => 'paymentReference',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, array('name' => 'paymentReference'))
                )
            ),
            'totalFrom' => array(
                'name' => 'totalFrom',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(DecimalValidator::class, array('name' => 'totalFrom'))
                )
            ),
            'totalTo' => array(
                'name' => 'totalTo',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(DecimalValidator::class, array('name' => 'totalTo'))
                )
            ),
            'currencyCode' => array(
                'name' => 'currencyCode',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, array('name' => 'currencyCode')),
                    $this->getDi()->newInstance(InArrayValidator::class, array('name' => 'currencyCode',
                        'haystack' => CurrencyCode::getCurrencyCodes()))
                )
            ),
            'buyerMessage' => array(
                'name' => 'buyerMessage',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(BooleanValidator::class, ['options' => ['name' => 'buyerMessage']])
                )
            )
        );
    }
}
