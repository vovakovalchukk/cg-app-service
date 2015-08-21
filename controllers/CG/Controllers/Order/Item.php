<?php
namespace CG\Controllers\Order;

use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Http\StatusCode;
use CG\Order\Service\Item\InvalidationService as ItemService;
use CG\Slim\Controller\Entity\PatchTrait;
use CG\Slim\ControllerTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;
use Slim\Slim;
use Zend\Di\Di;

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
