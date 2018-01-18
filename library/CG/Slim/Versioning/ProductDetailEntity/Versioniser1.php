<?php
namespace CG\Slim\Versioning\ProductDetailEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Product\Detail\Service;
use CG\Product\Detail\Entity as ProductDetail;

class Versioniser1 implements VersioniserInterface
{
    protected const NEW_FIELDS = ['description', 'ean', 'brand', 'mpn', 'asin', 'price', 'cost', 'condition'];

    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        $shouldReturn = true;
        foreach (static::NEW_FIELDS as $field) {
            if (!isset($data[$field])) {
                $shouldReturn = false;
                break;
            }
        }
        if ($shouldReturn) {
            return;
        }

        foreach (static::NEW_FIELDS as $field) {
            $data[$field] = null;
        }

        if (isset($params['productId'])) {
            try {
                /** @var ProductDetail $productDetail */
                $productDetail = $this->service->fetch($params['id']);
                $data['description'] = $productDetail->getDescription();
                $data['ean'] = $productDetail->getEan();
                $data['brand'] = $productDetail->getBrand();
                $data['mpn'] = $productDetail->getMpn();
                $data['asin'] = $productDetail->getAsin();
                $data['price'] = $productDetail->getPrice();
                $data['cost'] = $productDetail->getCost();
                $data['condition'] = $productDetail->getCondition();
            } catch (NotFound $exception) {
                // Entity not found so no information to copy
            }
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        foreach (static::NEW_FIELDS as $field) {
            unset($data[$field]);
        }
        $response->setData($data);
    }
}