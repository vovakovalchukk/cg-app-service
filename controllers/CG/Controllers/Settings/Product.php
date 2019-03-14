<?php
namespace CG\Controllers\Settings;

use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Http\StatusCode;
use CG\Settings\Product\RestService as Service;
use CG\Slim\ControllerTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use NoCarrier\Hal;
use Slim\Slim;

class Product implements LoggerAwareInterface
{
    use ControllerTrait, LogTrait;

    public function __construct(Slim $app, Service $service)
    {
        $this->setSlim($app)->setService($service);
    }

    public function get($id)
    {
        try {
            return $this->getService()->fetchAsHal($id);
        } catch (NotFound $exception) {
            throw new HttpNotFound(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }

    public function put($id, Hal $hal)
    {
        return $this->getService()->saveHal($hal, ['id' => $id]);
    }

    public function delete($id)
    {
        try {
            $this->getService()->removeById($id);
            $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
        } catch (NotFound $exception) {
            throw new HttpNotFound(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }
}
