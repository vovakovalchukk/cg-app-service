<?php
namespace CG\Controllers\Settings\Invoice;

use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Slim\ControllerTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use Slim\Slim;
use Zend\Di\Di;

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
