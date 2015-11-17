<?php
namespace CG\InputValidation\OrderCounts;

use CG\Locale\CurrencyCode;
use CG\Locale\CountryCode;
use CG\Validation\RulesInterface;
use CG\Validation\Rules\DecimalValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use Zend\Validator\Date;
use Zend\Validator\GreaterThan;
use Zend\Validator\InArray;
use Zend\Validator\StringLength;
use Zend\Validator\Callback;

use CG\Order\Shared\OrderCounts\Repository as OrderCountsRepository;

class Entity implements RulesInterface
{
    protected $orderRepository;

    public function __construct(OrderCountsRepository $orderCountsRepository)
    {
        $this->setOrderCountsRepository($orderCountsRepository);
    }


    ///WHAT FIELDS ARE ALLOWED TO BE SENT
    public function getRules()
    {
        $orderCountsRepository = $this->getOrderCountsRepository();
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
                'validators' => [new IsArrayValidator(["name" => "tag"])]
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
                'validators' => [new IsArrayValidator(["name" => "cancellations"])]
            ),
            'archived' => array(
                'name' => 'archived',
                'required' => false,
                'validators' => [
                    (new Callback())->setCallback(
                        function($archivedValue, $orderDetails) use($orderCountsRepository) {
                        $storedOrder = $orderCountsRepository->fetch($orderDetails['id']);
                        return $storedOrder->getArchived() == $archivedValue;
                    }
                    )->setMessage('Archived value cannot be modified')
                ]
            ),
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
        );
    }

    protected function setOrderCountsRepository($orderCountsRepository)
    {
        $this->orderCountsRepository = $orderCountsRepository;
        return $this;
    }

    protected function getOrderCountsRepository()
    {
        return $this->orderCountsRepository;
    }
}