<?php
namespace CG\Controllers\Settings;

use CG\Settings\Alias\Service;
use CG\Slim\ControllerTrait;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Http\StatusCode;
use Slim\Slim;
use Zend\Di\Di;

class Alias
{
    use ControllerTrait;

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get($organisationUnitId, $id)
    {
        try {
            return $this->getService()->fetchAsHal($id);
        } catch(NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function put($organisationUnitId, $id, Hal $hal)
    {
        return $this->getService()->saveHal($hal, ["id" => $id]);
    }

    public function delete($organisationUnitId, $id)
    {
        try {
            $this->getService()->removeById($id);
            $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}