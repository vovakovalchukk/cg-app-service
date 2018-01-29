<?php
namespace CG\Slim\Versioning\CategoryEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Product\Category\Service;
use CG\Product\Category\Entity as Category;

class Versioniser1 implements VersioniserInterface
{
    /** @var Service */
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['id']) || isset($data['accountId'])) {
            return;
        }

        try {
            /** @var Category $category */
            $category = $this->service->fetch($data['id']);
            $data['accountId'] = $category->getAccountId();
            $request->setData($data);
        } catch (NotFound $exception) {
            // New entity, nothing to copy over
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['accountId']);
        $response->setData($data);
    }
}