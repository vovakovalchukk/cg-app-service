<?php
namespace CG\Controllers\App\Service;

use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Http\Exception as HttpException;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Http\StatusCode;
use Nocarrier\Hal;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use CG\App\Service\Service;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait;

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get()
    {
        try {
            return $this->getService()->fetchCollectionWithPaginationAsHal(
                $this->getParams('limit'),
                $this->getParams('page')
            );
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function post(Hal $hal)
    {
        $hal = $this->getService()->saveHal($hal, array());
        $this->getSlim()->response()->setStatus(StatusCode::CREATED);
        return $hal;
    }
}
