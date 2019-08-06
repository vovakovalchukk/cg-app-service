<?php
namespace CG\InputValidation\Order\Order;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\RulesInterface;
use CG\Validation\ExclusionInterface;
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
use CG\Validation\Rules\IntegerValidator;

class Filter implements RulesInterface, ExclusionInterface
{
    use PaginationTrait;

    // In this instance, we want 'orderFilter' to be exclusive to
    // everything else, except for 'limit' and 'page' so the
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
                    new IsArrayValidator(['name' => 'orderIds'])
                ]
            ],
            'purchaseDateFrom' => [
                'name' => 'purchaseDateFrom',
                'required' => false,
                'validators' => [
                    new Date(['format' => 'Y-m-d H:i:s'])
                ]
            ],
            'purchaseDateTo' => [
                'name' => 'purchaseDateTo',
                'required' => false,
                'validators' => [
                    new Date(['format' => 'Y-m-d H:i:s'])
                ]
            ],
            'organisationUnitId' => [
                'name' => 'organisationUnitId',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
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
                    new IsArrayValidator(['name' => 'status'])
                ]
            ],
            'accountId' => [
                'name' => 'accountId',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'accountId')
                ]
            ],
            'channel' => [
                'name' => 'channel',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'channel'])
                ]
            ],
            'archived' => [
                'name' => 'archived',
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    new BooleanValidator(['name' => 'archived'])
                ]
            ],
            'shippingAddressCountry' => [
                'name' => 'shippingAddressCountry',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'shippingAddressCountry', 'haystack' => CountryCode::getCountryCodes()])
                ]
            ],
            'shippingAddressCountryExclude' => [
                'name' => 'shippingAddressCountryExclude',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'shippingAddressCountryExclude', 'haystack' => CountryCode::getCountryCodes()])
                ]
            ],
            'shippingAddressPostcode' => [
                'name' => 'shippingAddressPostcode',
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    new IsArrayValidator(['name' => 'shippingAddressPostCode'])
                ]
            ],
            'multiLineOrder' => [
                'name' => 'multiLineOrder',
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    new BooleanValidator(['name' => 'multiLineOrder'])
                ]
            ],
            'multiSameItem' => [
                'name' => 'multiSameItem',
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    new BooleanValidator(['name' => 'multiSameItem'])
                ]
            ],
            'shippingMethod' => [
                'name' => 'shippingMethod',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'shippingMethod'])
                ]
            ],
            'batch' => [
                'name' => 'batch',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'batch'])
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
                    (new InArray())->setHaystack(['ASC','DESC','asc','desc'])
                ]
            ],
            'tag' => [
                'name' => 'tag',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'tag'])
                ]
            ],
            'paymentMethod' => [
                'name' => 'paymentMethod',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'paymentMethod'])
                ]
            ],
            'paymentReference' => [
                'name' => 'paymentReference',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'paymentReference'])
                ]
            ],
            'totalFrom' => [
                'name' => 'totalFrom',
                'required' => false,
                'validators' => [new DecimalValidator(['name' => 'totalFrom'])]
            ],
            'totalTo' => [
                'name' => 'totalTo',
                'required' => false,
                'validators' => [new DecimalValidator(['name' => 'totalTo'])]
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
                'allow_empty' => true,
                'validators' => [
                    new BooleanValidator(['name' => 'buyerMessage'])
                ]
            ],
            'giftMessage' => [
                'name' => 'giftMessage',
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    new BooleanValidator(['name' => 'giftMessage'])
                ]
            ],
            'hasItems' => [
                'name' => 'hasItems',
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    new BooleanValidator(['name' => 'hasItems'])
                ]
            ],
            'fulfilmentChannel' => [
                'name' => 'fulfilmentChannel',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'fulfilmentChannel'])
                ]
            ],
            'invoiceNumber' => [
                'name' => 'invoiceNumber',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'invoiceNumber')
                ]
            ],
            'rootOrganisationUnitId' => [
                'name' => 'rootOrganisationUnitId',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'rootOrganisationUnitId')
                ]
            ],
            'externalId' => [
                'name' => 'externalId',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'externalId'])
                ]
            ],
            'externalUsername' => [
                'name' => 'externalUsername',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'externalUsername'])
                ]
            ],
            'id' => [
                'name' => 'id',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'convertToOrderIds' => [
                'name' => 'convertToOrderIds',
                'required' => false,
                'allow_empty' => true,
                'validators' => [new BooleanValidator(['name' => 'convertToOrderIds'])]
            ],
            'customer' => [
                'name' => 'customer',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'sequenceNumber' => [
                'name' => 'sequenceNumber',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'sequenceNumber')
                ]
            ],
            'dispatchDateFrom' => [
                'name' => 'dispatchDateFrom',
                'required' => false,
                'validators' => [
                    new Date(['format' => 'Y-m-d H:i:s'])
                ]
            ],
            'dispatchDateTo' => [
                'name' => 'dispatchDateTo',
                'required' => false,
                'validators' => [
                    new Date(['format' => 'Y-m-d H:i:s'])
                ]
            ],
            'hasInvoice' => [
                'name' => 'hasInvoice',
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    new BooleanValidator(['name' => 'hasInvoice'])
                ]
            ],
            'weightMin' => [
                'name' => 'weightMin',
                'required' => false,
                'validators' => [new DecimalValidator(['name' => 'weightMin'])]
            ],
            'weightMax' => [
                'name' => 'weightMax',
                'required' => false,
                'validators' => [new DecimalValidator(['name' => 'weightMax'])]
            ],
            'dispatchable' => [
                'name' => 'dispatchable',
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    new BooleanValidator(['name' => 'dispatchable'])
                ]
            ],
            'marketplace' => [
                'name' => 'marketplace',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'marketplace'])
                ]
            ],
            'invoicePrinted' => [
                'name' => 'invoicePrinted',
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    new BooleanValidator(['name' => 'invoicePrinted'])
                ]
            ],
            'invoiceEmailed' => [
                'name' => 'invoiceEmailed',
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    new BooleanValidator(['name' => 'invoiceEmailed'])
                ]
            ],
        ];
    }
}
