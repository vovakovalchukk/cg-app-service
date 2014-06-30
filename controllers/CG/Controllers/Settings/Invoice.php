<?php
namespace CG\Controllers\Settings;

use CG\Http\StatusCode;
use CG\Settings\Service\Invoice\Service;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;
use Nocarrier\Hal;

class Invoice
{
    use ControllerTrait;

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this->setSlim($app)
             ->setService($service)
             ->setDi($di);
    }

    public function get($id)
    {
        return $this->getService()->fetchAsHal($id);
    }

    public function put($id, Hal $hal)
    {
        return $this->getService()->saveHal($hal, array("organisationUnitId" => $id));
    }

    public function delete($id)
    {
        $this->getService()->removeById($id);
        $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
    }
}
