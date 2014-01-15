<?php
namespace CG\Controllers\Order;

use CG\Http\StatusCode;
use CG\Order\Service\Tag\Service as TagService;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;
use Nocarrier\Hal;

class Tag
{
    use ControllerTrait;

    public function __construct(Slim $app, TagService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get($TagId)
    {
        return $this->getService()->fetchAsHal($TagId);
    }

    public function put($TagId, Hal $hal)
    {
        return $this->getService()->saveHal($hal, array("id" => $TagId));
    }

    public function delete($TagId)
    {
        $this->getService()->removeById($TagId);
        $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
    }
}