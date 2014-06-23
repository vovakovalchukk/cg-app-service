<?php
namespace CG\Controllers\Settings\Invoice;

use CG\Http\StatusCode;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;
use CG\Slim\Renderer\ResponseType\Hal;

class Collection
{
    use ControllerTrait;

    public function __construct(Slim $app, Di $di)
    {
        $this->setSlim($app)
             ->setDi($di);
    }

    public function get()
    {
        try {
            return $this->getService()->fetchCollectionByPagination(
                $this->getParams('limit'),
                $this->getParams('page')
            );
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}
