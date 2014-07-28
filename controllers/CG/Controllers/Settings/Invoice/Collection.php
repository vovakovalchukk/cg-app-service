<?php
namespace CG\Controllers\Settings\Invoice;

use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Slim\ControllerTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use Slim\Slim;
use CG\Settings\Invoice\Service\Service;

class Collection
{
    use ControllerTrait;

    public function __construct(Slim $app, Service $service)
    {
        $this->setSlim($app)
             ->setService($service);
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
