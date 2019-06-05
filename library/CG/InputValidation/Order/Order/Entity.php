<?php
namespace CG\InputValidation\Order\Order;

use CG\Locale\CountryCode;
use CG\Locale\CurrencyCode;
use CG\Order\Shared\Repository as OrderRepository;
use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\DecimalValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\Date;
use Zend\Validator\GreaterThan;
use Zend\Validator\InArray;
use Zend\Validator\StringLength;

class Entity implements RulesInterface
{
    protected $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->setOrderRepository($orderRepository);
    }

    public function getRules()
    {
        $orderRepository = $this->getOrderRepository();
        return array(
            'id' => array(
                'name'       => 'id',
                'required'   => false,
                'validators' => []
            ),
            'accountId' => array(
                'name'       => 'accountId',
                'validators' => [
                    new IntegerValidator(['name' => 'accountId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'accountId must be at least %min%'])
                ]
            ),
            'externalId' => array(
                'name'       => 'externalId',
                'validators' => [new StringLength(['min' => 1])]
            ),
            'channel' => array(
                'name'       => 'note',
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
            'total' => array(
                'name'       => 'total',
                'validators' => [new DecimalValidator(['name' => 'total'])]
            ),
            'totalDiscount' => array(
                'name'       => 'totalDiscount',
                'validators' => [new DecimalValidator(['name' => 'totalDiscount'])]
            ),
            'discountDescription' => array(
                'name'       => 'discountDescription',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ),
            'status' => array(
                'name'       => 'status',
                'validators' => [new StringLength(['min' => 1])]
            ),
            'shippingPrice' => array(
                'name'       => 'shippingPrice',
                'validators' => [new DecimalValidator(['name' => 'shippingPrice'])]
            ),
            'shippingMethod' => array(
                'name'       => 'shippingMethod',
                'validators' => [new StringLength(['min' => 1])]
            ),
            'currencyCode' => array(
                'name'       => 'currencyCode',
                'required'   => false,
                'validators' => [(new InArray())->setHaystack(CurrencyCode::getCurrencyCodes())]
            ),
            'buyerMessage' => array(
                'name'       => 'buyerMessage',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ),
            'purchaseDate' => array(
                'name'       => 'purchaseDate',
                'validators' => [new Date(['format' => 'Y-m-d H:i:s'])]
            ),
            'paymentDate' => array(
                'name'       => 'paymentDate',
                'required'   => false,
                'validators' => [new Date(['format' => 'Y-m-d H:i:s'])]
            ),
            'printedDate' => array(
                'name'       => 'printedDate',
                'required'   => false,
                'validators' => [new Date(['format' => 'Y-m-d H:i:s'])]
            ),
            'emailDate' => array(
                'name'       => 'emailDate',
                'required'   => false,
                'validators' => [new Date(['format' => 'Y-m-d H:i:s'])]
            ),
            'dispatchDate' => array(
                'name'       => 'dispatchDate',
                'required'   => false,
                'validators' => [new Date(['format' => 'Y-m-d H:i:s'])]
            ),
            'custom' => array(
                'name'       => 'custom',
                'required'   => false,
                'validators' => []
            ),
            'billingAddressRedacted'  => [
                'name'       => 'billingAddressRedacted',
                'required'   => false,
                'validators' => [new BooleanValidator(['name' => 'billingAddressRedacted'])],
            ],
            'billingAddressCompanyName'  => array(
                'name'       => 'billingAddressCompanyName',
                'required'   => false,
                'validators' => []
            ),
            'billingAddressFullName'  => array(
                'name'       => 'billingAddressFullName',
                'required'   => false,
                'validators' => []
            ),
            'billingAddress1'  => array(
                'name'       => 'billingAddress1',
                'required'   => false,
                'validators' => []
            ),
            'billingAddress2'  => array(
                'name'       => 'billingAddress2',
                'required'   => false,
                'validators' => []
            ),
            'billingAddress3'  => array(
                'name'       => 'billingAddress3',
                'required'   => false,
                'validators' => []
            ),
            'billingAddressCity'  => array(
                'name'       => 'billingAddressCity',
                'required'   => false,
                'validators' => []
            ),
            'billingAddressCounty'  => array(
                'name'       => 'billingAddressCounty',
                'required'   => false,
                'validators' => []
            ),
            'billingAddressCountry'  => array(
                'name'       => 'billingAddressCountry',
                'required'   => false,
                'validators' => []
            ),
            'billingAddressPostcode'  => array(
                'name'       => 'billingAddressPostcode',
                'required'   => false,
                'validators' => []
            ),
            'billingAddressCountryCode'  => array(
                'name'       => 'billingAddressCountryCode',
                'required'   => false,
                'validators' => [(new InArray())->setHaystack(CountryCode::getCountryCodes())]
            ),
            'billingEmailAddress'  => array(
                'name'       => 'billingEmailAddress',
                'required'   => false,
                'validators' => []
            ),
            'billingPhoneNumber'  => array(
                'name'       => 'billingPhoneNumber',
                'required'   => false,
                'validators' => []
            ),
            'billingExternalId'  => array(
                'name'       => 'billingExternalId',
                'required'   => false,
                'validators' => []
            ),
            'shippingAddressRedacted'  => [
                'name'       => 'shippingAddressRedacted',
                'required'   => false,
                'validators' => [new BooleanValidator(['name' => 'shippingAddressRedacted'])],
            ],
            'shippingAddressCompanyName'  => array(
                'name'       => 'shippingAddressCompanyName',
                'required'   => false,
                'validators' => []
            ),
            'shippingAddressFullName'  => array(
                'name'       => 'shippingAddressFullName',
                'required'   => false,
                'validators' => []
            ),
            'shippingAddress1'  => array(
                'name'       => 'shippingAddress1',
                'required'   => false,
                'validators' => []
            ),
            'shippingAddress2'  => array(
                'name'       => 'shippingAddress2',
                'required'   => false,
                'validators' => []
            ),
            'shippingAddress3'  => array(
                'name'       => 'shippingAddress3',
                'required'   => false,
                'validators' => []
            ),
            'shippingAddressCity'  => array(
                'name'       => 'shippingAddressCity',
                'required'   => false,
                'validators' => []
            ),
            'shippingAddressCounty'  => array(
                'name'       => 'shippingAddressCounty',
                'required'   => false,
                'validators' => []
            ),
            'shippingAddressCountry'  => array(
                'name'       => 'shippingAddressCountry',
                'required'   => false,
                'validators' => []
            ),
            'shippingAddressPostcode'  => array(
                'name'       => 'shippingAddressPostcode',
                'required'   => false,
                'validators' => []
            ),
            'shippingAddressCountryCode'  => array(
                'name'       => 'shippingAddressCountryCode',
                'required'   => false,
                'validators' => [(new InArray())->setHaystack(CountryCode::getCountryCodes())]
            ),
            'shippingEmailAddress'  => array(
                'name'       => 'shippingEmailAddress',
                'required'   => false,
                'validators' => []
            ),
            'shippingPhoneNumber'  => array(
                'name'       => 'shippingPhoneNumber',
                'required'   => false,
                'validators' => []
            ),
            'shippingExternalId'  => array(
                'name'       => 'shippingExternalId',
                'required'   => false,
                'validators' => []
            ),
            'fulfilmentAddressRedacted'  => [
                'name'       => 'fulfilmentAddressRedacted',
                'required'   => false,
                'validators' => [new BooleanValidator(['name' => 'fulfilmentAddressRedacted'])],
            ],
            'fulfilmentAddressCompanyName'  => array(
                'name'       => 'fulfilmentAddressCompanyName',
                'required'   => false,
                'validators' => []
            ),
            'fulfilmentAddressFullName'  => array(
                'name'       => 'fulfilmentAddressFullName',
                'required'   => false,
                'validators' => []
            ),
            'fulfilmentAddress1'  => array(
                'name'       => 'fulfilmentAddress1',
                'required'   => false,
                'validators' => []
            ),
            'fulfilmentAddress2'  => array(
                'name'       => 'fulfilmentAddress2',
                'required'   => false,
                'validators' => []
            ),
            'fulfilmentAddress3'  => array(
                'name'       => 'fulfilmentAddress3',
                'required'   => false,
                'validators' => []
            ),
            'fulfilmentAddressCity'  => array(
                'name'       => 'fulfilmentAddressCity',
                'required'   => false,
                'validators' => []
            ),
            'fulfilmentAddressCounty'  => array(
                'name'       => 'fulfilmentAddressCounty',
                'required'   => false,
                'validators' => []
            ),
            'fulfilmentAddressCountry'  => array(
                'name'       => 'fulfilmentAddressCountry',
                'required'   => false,
                'validators' => []
            ),
            'fulfilmentAddressPostcode'  => array(
                'name'       => 'fulfilmentAddressPostcode',
                'required'   => false,
                'validators' => []
            ),
            'fulfilmentAddressCountryCode'  => array(
                'name'       => 'fulfilmentAddressCountryCode',
                'required'   => false,
                'validators' => [(new InArray())->setHaystack(CountryCode::getCountryCodes())]
            ),
            'fulfilmentEmailAddress'  => array(
                'name'       => 'fulfilmentEmailAddress',
                'required'   => false,
                'validators' => []
            ),
            'fulfilmentPhoneNumber'  => array(
                'name'       => 'fulfilmentPhoneNumber',
                'required'   => false,
                'validators' => []
            ),
            'fulfilmentExternalId'  => array(
                'name'       => 'fulfilmentExternalId',
                'required'   => false,
                'validators' => []
            ),
            'batch' => array(
                'name'       => 'batch',
                'required'   => false,
                'validators' => [
                    new IntegerValidator(['name' => 'batch']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'batch must be at least %min%'])
                ]
            ),
            'tag' => array (
                'name' => 'tag',
                'required' => false,
                'validators' => [new IsArrayValidator(['name' => 'tag'])]
            ),
            'paymentMethod' => array(
                'name'       => 'paymentMethod',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ),
            'paymentReference' => array(
                'name'       => 'paymentReference',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ),
            'cancellations'  => array(
                'name'       => 'cancellations',
                'required'   => false,
                'validators' => [new IsArrayValidator(['name' => 'cancellations'])]
            ),
            'archived' => [
                'name'       => 'archived',
                'required'   => false,
                'allow_empty' => true,
                'validators' => [new BooleanValidator(['name' => 'archived'])],
            ],
            'fulfilmentChannel' => array(
                'name' => 'fulfilmentChannel',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ),
            'invoiceNumber' => [
                'name'       => 'invoiceNumber',
                'required'   => false,
                'validators' => [
                    new IntegerValidator(['name' => 'invoiceNumber']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'invoiceNumber must be at least %min%'])
                ]
            ],
            'vatNumber' => array(
                'name' => 'vatNumber',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ),
            'sequenceNumber' => [
                'name'       => 'sequenceNumber',
                'required'   => false,
                'validators' => [
                    new IntegerValidator(['name' => 'sequenceNumber']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'sequenceNumber must be at least %min%'])
                ]
            ],
            'rootOrganisationUnitId' => [
                'name'       => 'rootOrganisationUnitId',
                'required'   => false,
                'validators' => [
                    new IntegerValidator(['name' => 'rootOrganisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'rootOrganisationUnitId must be at least %min%'])
                ]
            ],
            'lastUpdateFromChannel' => array(
                'name'       => 'lastUpdateFromChannel',
                'required'   => false,
                'validators' => [new Date(['format' => 'Y-m-d H:i:s'])]
            ),
            'externalUsername' => [
                'name' => 'externalUsername',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'recipientVatNumber' => [
                'name' => 'recipientVatNumber',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'shippingOriginCountryCode' => [
                'name' => 'shippingOriginCountryCode',
                'required' => false,
                'validators' => [(new InArray())->setHaystack(CountryCode::getCountryCodes())]
            ],
            'marketplace' => [
                'name'       => 'marketplace',
                'required'   => false,
                'validators' => []
            ],
            'exchangeRate' => [
                'name'       => 'exchangeRate',
                'required'   => false,
                'validators' => [new DecimalValidator(['name' => 'exchangeRate'])]
            ],
            'exchangeRateCurrencyCode' => array(
                'name'       => 'exchangeRateCurrencyCode',
                'required'   => false,
                'validators' => [(new InArray())->setHaystack(CurrencyCode::getCurrencyCodes())]
            ),
            'weight' => [
                'name' => 'weight',
                'required' => false,
                'validators' => [new DecimalValidator(['name' => 'weight'])]
            ],
            'dispatchable' => [
                'name' => 'dispatchable',
                'required' => false,
                'validators' => [new BooleanValidator(['name' => 'dispatchable'])]
            ]
        );
    }

    protected function setOrderRepository($orderRepository)
    {
        $this->orderRepository = $orderRepository;
        return $this;
    }

    protected function getOrderRepository()
    {
        return $this->orderRepository;
    }
}
