<?php
namespace CG\Controllers\UserPreference;

use CG\Http\StatusCode;
use CG\UserPreference\Service\Service as BatchService;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;
use Nocarrier\Hal;

class UserPreference
{
    use ControllerTrait;

    public function __construct(Slim $app, BatchService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get($userId)
    {
        return $this->getService()->fetchAsHal($userId);
    }

    public function put($userId, Hal $hal)
    {
        return $this->getService()->saveHal($hal, array("id" => $userId));
    }

    public function delete($userId)
    {
        $this->getService()->removeById($userId);
        $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
    }
}
