<?php
namespace CG\Controllers\Order;

use CG\Http\StatusCode;
use CG\Order\Service\Item\Service as ItemService;
use CG\Slim\ControllerTrait;
use CG\Slim\Controller\Entity\PatchTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;
use Nocarrier\Hal;

class Item
{
    use ControllerTrait;
    use PatchTrait;

    public function __construct(Slim $app, ItemService $service, Di $di)
    {
        $this->setSlim($app)
             ->setService($service)
             ->setDi($di);
    }

    public function get($id)
    {
        try {
            return $this->getService()->fetchAsHal($id);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function put($id, Hal $hal)
    {
        return $this->getService()->saveHal($hal, array("id" => $id));
    }

    public function delete($id)
    {
        try {
            $this->getService()->removeById($id);
            $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}
