<?php
namespace CG\Controllers\Settings\Product;

use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Settings\Product\Filter;
use CG\Settings\Product\RestService as Service;
use CG\Slim\ControllerTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use Slim\Slim;

class Collection
{
    use ControllerTrait;

    public function __construct(Slim $app, Service $service)
    {
        $this->setSlim($app)->setService($service);
    }

    public function get()
    {
        try {
            return $this->getService()->fetchCollectionAsHal(
                new Filter(
                    $this->getParams('limit'),
                    $this->getParams('page'),
                    $this->getParams('id') ?: []
                )
            );
        } catch (NotFound $exception) {
            throw new HttpNotFound(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }
} 
