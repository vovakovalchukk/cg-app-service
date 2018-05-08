<?php
namespace CG\Slim\Versioning\CategoryTemplateEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;
use CG\Product\Category\Template\Entity as CategoryTemplate;

class Versioniser1 implements VersioniserInterface
{
    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        unset($data['categoryIds']);
        $data['accounts'] = [];
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        if (empty($data['accounts'])) {
            return;
        }
        foreach ($data['accounts'] as $category) {
            $data['categoryIds'][] = $category[CategoryTemplate::KEY_CATEGORY_ID];
        }
        unset($data['accounts']);
        $response->setData($data);
    }
}
