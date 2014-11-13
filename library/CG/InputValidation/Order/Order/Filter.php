<?php
namespace CG\InputValidation\Order\Order;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\PaginationTrait;
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
    use PaginationTrait;

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
        unset($rules['orderBy']);
        unset($rules['orderDirection']);
        $ruleKeys = array_keys($rules);
        $excludeOthers = array_fill_keys($ruleKeys, true);
        $excludeFilter = array_fill_keys($ruleKeys, ['orderFilter' => true]);
        unset($excludeFilter['orderFilter']);
        return array_merge($excludeFilter, ['orderFilter' => $excludeOthers]);
    }

    public function getRules()
    {
        return [
            'limit' => $this->getLimitValidation(),
            'page' => $this->getPageValidation(),
            'orderFilter' => [
                'name' => 'orderFilter',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'orderIds' => [
                'name' => 'orderIds',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "orderIds"])
                ]
            ],
            'purchaseDateFrom' => [
                'name' => 'purchaseDateFrom',
                'required' => false,
                'validators' => [
                    new Date(['format' => "Y-m-d H:i:s"])
                ]
            ],
            'purchaseDateTo' => [
                'name' => 'purchaseDateTo',
                'required' => false,
                'validators' => [
                    new Date(['format' => "Y-m-d H:i:s"])
                ]
            ],
            'organisationUnitId' => [
                'name' => 'organisationUnitId',
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(ArrayOfIntegersValidator::class, ["name" => "organisationUnitId"])
                ]
            ],
            'searchTerm' => [
                'name' => 'searchTerm',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'status' => [
                'name' => 'status',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "status"])
                ]
            ],
            'accountId' => [
                'name' => 'accountId',
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(ArrayOfIntegersValidator::class, ["name" => "accountId"])
                ]
            ],
            'channel' => [
                'name' => 'channel',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "channel"])
                ]
            ],
            'archived' => [
                'name' => 'archived',
                'required' => false,
                'validators' => [
                    new BooleanValidator(["name" => "archived"])
                ]
            ],
            'shippingAddressCountry' => [
                'name' => 'shippingAddressCountry',
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(IsArrayValidator::class, ['name' => 'shippingAddressCountry']),
                    $this->getDi()->newInstance(InArrayValidator::class, ['name' => 'shippingAddressCountry',
                                                                               'haystack' => CountryCode::getCountryCodes()])
                ]
            ],
            'shippingAddressCountryExclude' => [
                'name' => 'shippingAddressCountryExclude',
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(IsArrayValidator::class, ['name' => 'shippingAddressCountryExclude']),
                    $this->getDi()->newInstance(InArrayValidator::class, ['name' => 'shippingAddressCountryExclude',
                                                                               'haystack' => CountryCode::getCountryCodes()])
                ]
            ],
            'multiLineOrder' => [
                'name' => 'multiLineOrder',
                'required' => false,
                'validators' => [
                    new BooleanValidator(["name" => "multiLineOrder"])
                ]
            ],
            'multiSameItem' => [
                'name' => 'multiSameItem',
                'required' => false,
                'validators' => [
                    new BooleanValidator(["name" => "multiSameItem"])
                ]
            ],
            'shippingMethod' => [
                'name' => 'shippingMethod',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "shippingMethod"])
                ]
            ],
            'batch' => [
                'name' => 'batch',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "batch"])
                ]
            ],
            'orderBy' => [
                'name' => 'orderBy',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'orderDirection' => [
                'name' => 'orderDirection',
                'required' => false,
                'validators' => [
                    (new InArray())->setHaystack(['ASC','DESC'])
                ]
            ],
            'tag' => [
                'name' => 'tag',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "tag"])
                ]
            ],
            'paymentMethod' => [
                'name' => 'paymentMethod',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "paymentMethod"])
                ]
            ],
            'paymentReference' => [
                'name' => 'paymentReference',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "paymentReference"])
                ]
            ],
            'totalFrom' => [
                'name' => 'totalFrom',
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(DecimalValidator::class, ['name' => 'totalFrom'])
                ]
            ],
            'totalTo' => [
                'name' => 'totalTo',
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(DecimalValidator::class, ['name' => 'totalTo'])
                ]
            ],
            'currencyCode' => [
                'name' => 'currencyCode',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'currencyCode', 'haystack' => CurrencyCode::getCurrencyCodes()])
                ]
            ],
            'buyerMessage' => [
                'name' => 'buyerMessage',
                'required' => false,
                'validators' => [
                    new BooleanValidator(["name" => "buyerMessage"])
                ]
            ],
            'hasItems' => [
                'name' => 'hasItems',
                'required' => false,
                'validators' => [
                    new BooleanValidator(['name' => 'hasItems'])
                ]
            ]
        ];
    }
}