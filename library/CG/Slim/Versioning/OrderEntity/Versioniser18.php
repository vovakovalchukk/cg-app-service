<?php
namespace CG\Slim\Versioning\OrderEntity;

use CG\Order\Service\Service;
use CG\Order\Shared\Address\Redacted as AddressRedacted;
use CG\Order\Shared\AddressInterface as Address;
use CG\Order\Shared\Entity as Order;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser18 implements VersioniserInterface
{
    /** @var Service $service */
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();

        try {
            /** @var Order $order */
            $order = $this->service->fetch($data['id'] ?? null);
        } catch (NotFound $e) {
            // New order so there won't be a previously set value
            $order = null;
        }

        foreach (['billing', 'shipping', 'fulfilment'] as $address) {
            $this->redactAddress(
                $data,
                $address,
                $order ? $order->{'get' . ucfirst($address) . 'Address'}() : null
            );
        }

        $request->setData($data);
    }

    protected function redactAddress(array &$data, string $type, ?Address $address): void
    {
        $redacted = $address ? $address->isRedacted() : false;
        $data[$type . 'AddressRedacted'] = $data[$type . 'AddressRedacted'] ?? $redacted;

        if (!$data[$type . 'AddressRedacted'] || $data[$type . 'Address1'] !== AddressRedacted::ADDRESS_1) {
            return;
        }

        unset(
            $data[$type . 'AddressCompanyName'],
            $data[$type . 'AddressFullName'],
            $data[$type . 'Address1'],
            $data[$type . 'Address2'],
            $data[$type . 'Address3'],
            $data[$type . 'AddressCity'],
            $data[$type . 'AddressCounty'],
            $data[$type . 'AddressCountry'],
            $data[$type . 'AddressPostcode'],
            $data[$type . 'EmailAddress'],
            $data[$type . 'PhoneNumber'],
            $data[$type . 'AddressCountryCode'],
            $data[$type . 'ExternalId']
        );
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['billingAddressRedacted'], $data['shippingAddressRedacted'], $data['fulfilmentAddressRedacted']);
        $response->setData($data);
    }
}
