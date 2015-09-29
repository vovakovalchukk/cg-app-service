<?php
namespace CG\Controllers\Stock;

use CG\CGLib\Nginx\Cache\Invalidator\ProductStock as Invalidator;
use CG\Slim\Controller\Entity\DeleteTrait;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\ControllerTrait;
use CG\Stock\Mapper as StockMapper;
use CG\Stock\Service;
use Exception;
use Nocarrier\Hal;
use Slim\Slim;
use Zend\Di\Di;

class Stock
{
    use ControllerTrait;
    use GetTrait;
    use PutTrait {
        put as putTrait;
    }
    use DeleteTrait;
    use InvalidationTrait;

    protected $stockMapper;
    protected $invalidator;

    public function __construct(Slim $app, Service $service, Di $di, StockMapper $stockMapper, Invalidator $invalidator)
    {
        $this
            ->setSlim($app)
            ->setService($service)
            ->setDi($di)
            ->setStockMapper($stockMapper)
            ->setInvalidator($invalidator);
    }

    public function put($id, Hal $hal)
    {
        $stockHal = $this->putTrait($id, $hal);
        try {
            $this->invalidateStock($stockHal);
        } catch (Exception $exception) {
            // No-op. Save succeeded, everything else is superfluous
        }
        return $stockHal;
    }

    /**
     * @return self
     */
    protected function setStockMapper(StockMapper $stockMapper)
    {
        $this->stockMapper = $stockMapper;
        return $this;
    }

    /**
     * @return StockMapper
     */
    protected function getStockMapper()
    {
        return $this->stockMapper;
    }

    /**
     * @return self
     */
    protected function setInvalidator(Invalidator $invalidator)
    {
        $this->invalidator = $invalidator;
        return $this;
    }

    /**
     * @return Invalidator
     */
    protected function getInvalidator()
    {
        return $this->invalidator;
    }
}
