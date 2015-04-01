<?php
namespace CG\Controllers\Product\VariationAttributeMap;

use CG\Product\VariationAttributeMap\Service;
use CG\Slim\ControllerTrait;
use CG\Slim\Renderer\ResponseType\Hal;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Http\StatusCode;
use CG\Stdlib\Exception\Runtime\NotFound;
use Slim\Slim;
use Zend\Di\Di;

class VariationAttributeMap
{
    use ControllerTrait;

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get($productId, $variationAttributeMapId)
    {
        try {
            return $this->getService()->fetchAsHal($productId, $variationAttributeMapId);
        } catch(NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function put($productId, $variationAttributeMapId, Hal $hal)
    {
        return $this->getService()->saveHal($hal, ['id' => $variationAttributeMapId]);
    }

    public function delete($productId, $variationAttributeMapId)
    {
        try {
            $this->getService()->removeById($variationAttributeMapId);
            $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}
