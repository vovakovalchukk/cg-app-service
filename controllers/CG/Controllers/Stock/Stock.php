<?php
namespace CG\Controllers\Stock;

use CG\CGLib\Gearman\Generator\UpdateRelatedListingsForStock as UpdateRelatedListingsForStockGearmanJobGenerator;
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
        put as protected putTrait;
    }
    use DeleteTrait;
    use InvalidationTrait;

    /** @var StockMapper $stockMapper */
    protected $stockMapper;
    /** @var Invalidator $invalidator */
    protected $invalidator;
    /** @var UpdateRelatedListingsForStockGearmanJobGenerator $updateRelatedListingsForStock */
    protected $updateRelatedListingsForStock;

    public function __construct(
        Slim $app,
        Service $service,
        Di $di,
        StockMapper $stockMapper,
        Invalidator $invalidator,
        UpdateRelatedListingsForStockGearmanJobGenerator $updateRelatedListingsForStock
    ) {
        $this
            ->setSlim($app)
            ->setService($service)
            ->setDi($di)
            ->setStockMapper($stockMapper)
            ->setInvalidator($invalidator)
            ->setUpdateRelatedListingsForStock($updateRelatedListingsForStock);
    }

    public function put($id, Hal $hal)
    {
        $stockHal = $this->putTrait($id, $hal);
        try {
            $stock = $this->invalidateStock($this->stockMapper->fromHal($stockHal));
            $this->updateRelatedListingsForStock->generateJob($stock);
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

    /**
     * @return self
     */
    protected function setUpdateRelatedListingsForStock(
        UpdateRelatedListingsForStockGearmanJobGenerator $updateRelatedListingsForStock
    ) {
        $this->updateRelatedListingsForStock = $updateRelatedListingsForStock;
        return $this;
    }
}
