<?php
namespace CG\Controllers;

use CG\RestExample\ServiceInterface;
use CG\Slim\ControllerTrait;
use CG\Stdlib\Exception\Runtime\AlreadyExists;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Http\Exception\Exception4xx\Conflict as HttpConflict;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Http\StatusCode;
use Slim\Slim;
use Zend\Di\Di;

class RestExample
{
    use ControllerTrait;

    public function __construct(Slim $app, Di $di, ServiceInterface $service)
    {
        $this->setSlim($app)
            ->setDi($di)
            ->setService($service);
    }

    /**
     * get, put, post and delete methods are defined here.
     * each method should either:
     *  - return a CG\Slim\Renderer\ResponseType\Hal object
     *  - throw an exception
     *
     * throwing http exceptions will set the status code.
     * a CG\Stdlib\Exception\Runtime\FieldValidationMessagesException will set the status to 422
     * any other exceptions will set the status code to 500.
     *
     * If we are setting an status code but dont want to throw an exception, we do that like this:
     * $this->getSlim()->response()->setStatus(StatusCode::CREATED);
     */
    public function get()
    {
        try {
            $params = \CG\Stdlib\flatten($this->getParams());
            $this->getService()->validateInput($this->getDi(), $params);
            return $this->getService()->fetchAsHal($params['status']);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage());
        } catch (AlreadyExists $e) {
            throw new HttpConflict($e->getMessage());
        }
    }
}