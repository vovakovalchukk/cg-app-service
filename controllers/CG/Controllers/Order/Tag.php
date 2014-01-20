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

    public function get($tagId)
    {
        return $this->getService()->fetchAsHal($tagId);
    }

    public function put($tagId, Hal $hal)
    {
        return $this->getService()->saveHal($hal, array("id" => $tagId));
    }

    public function delete($tagId)
    {
        $this->getService()->removeById($tagId);
        $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
    }
}