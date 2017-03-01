<?php
namespace CG\Controllers\Order\Item;

use CG\Order\Service\Item\InvalidationService as ItemService;
use CG\Slim\ControllerTrait;
use Nocarrier\Hal;
use Slim\Slim;
use Zend\Di\Di;

/**
 * @method ItemService getService
 */
class Images
{
    use ControllerTrait;

    public function __construct(Slim $app, ItemService $service, Di $di)
    {
        $this
            ->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function put($id, Hal $hal)
    {
        $item = $this->getService()->fetch($id);
        return $this->getService()->updateImagesHalAsHal($item, $hal);
    }
}
