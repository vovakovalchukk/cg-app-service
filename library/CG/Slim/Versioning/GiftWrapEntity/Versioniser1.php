<?php
namespace CG\Slim\Versioning\GiftWrapEntity;

use CG\Order\Service\Item\GiftWrap\Service as Service;
use CG\Order\Shared\Item\GiftWrap\Entity as GiftWrap;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    protected const REDACTED = 'REDACTED';

    /** @var Service */
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();

        try {
            /** @var GiftWrap $giftWrap */
            $giftWrap = $this->service->fetch($data['id'] ?? null, $data['orderItemId'] ?? null);
        } catch (NotFound $e) {
            // New giftWrap so there won't be a previously set value
            $giftWrap = null;
        }

        $data['giftWrapRedacted'] = $data['giftWrapRedacted'] ?? ($giftWrap ? $giftWrap->isGiftWrapRedacted() : false);
        if (isset($data['giftWrapMessage'])) {
            $data['giftWrapMessage'] = $data['giftWrapMessage'] === static::REDACTED ? null : $data['giftWrapMessage'];
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        if (isset($data['giftWrapRedacted'])) {
            $data['giftWrapMessage'] = $data['giftWrapRedacted'] ? static::REDACTED : ($data['giftWrapMessage'] ?? null);
            unset($data['giftWrapRedacted']);
        }
        $response->setData($data);
    }
}