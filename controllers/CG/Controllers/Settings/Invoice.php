<?php
namespace CG\Controllers\Settings;

use CG\Http\StatusCode;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Settings\Invoice\Service;
use CG\Slim\ControllerTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use NoCarrier\Hal;
use Slim\Slim;

class Invoice implements LoggerAwareInterface
{
    use ControllerTrait, LogTrait;

    public function __construct(Slim $app, Service $service)
    {
        $this->setSlim($app)
             ->setService($service);
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
        $this->logDebug('Settings/Invoice/' . $id . ' was PUT');
        return $this->getService()->saveHal($hal, ['id' => $id]);
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
