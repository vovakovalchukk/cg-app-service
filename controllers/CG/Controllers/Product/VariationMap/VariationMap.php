<?php
namespace CG\Controllers\Product\VariationMap;

use CG\Product\Service\Service;
use CG\Slim\ControllerTrait;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Http\StatusCode;
use CG\Stdlib\Exception\Runtime\NotFound;
use Slim\Slim;
use Zend\Di\Di;

class VariationMap
{
    use ControllerTrait;

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get($productId, $variationMapId)
    {
        try {
            return $this->getService()->fetchAsHal($productId, $variationMapId);
        } catch(NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function put($productId, $variationMapId, Hal $hal)
    {
        return $this->getService()->saveHal($hal, ['id' => $variationMapId]);
    }

    public function delete($productId, $variationMapId)
    {
        try {
            $this->getService()->removeById($variationMapId);
            $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}
