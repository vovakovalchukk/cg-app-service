<?php
namespace CG\Controllers\Stock\Location;

use CG\CGLib\Listing\Status\Service as ListingStatusService;
use CG\Stock\Location\Mapper as StockLocationMapper;
use CG\Stock\Location\Service;
use CG\Stock\Service as StockService;
use CG\Slim\ControllerTrait;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\Controller\Entity\DeleteTrait;
use CG\Validation\PaginationInterface;
use Nocarrier\Hal;
use Slim\Slim;
use Zend\Di\Di;
use CG\CGLib\Nginx\Cache\Invalidator;

class Location implements PaginationInterface
{
    use ControllerTrait, GetTrait, PutTrait, DeleteTrait;

    protected $listingStatusService;
    protected $stockService;
    protected $stockLocationMapper;

    public function __construct(
        ListingStatusService $listingStatusService,
        StockLocationMapper $stockLocationMapper,
        Slim $app,
        Service $service,
        Di $di,
        StockService $stockService,
        Invalidator $invalidator
    ) {
        $this->setListingStatusService($listingStatusService)
            ->setStockLocationMapper($stockLocationMapper)
            ->setSlim($app)
            ->setService($service)
            ->setStockService($stockService)
            ->setDi($di)
            ->setInvalidator($invalidator);
    }

    public function put($id, Hal $hal)
    {
        $stockLocationHal = $this->getService()->saveHal($hal, ["id" => $id]);
        $stockLocation = $this->getStockLocationMapper()->fromHal($stockLocationHal);
        $stockId = $stockLocation->getStockId();
        $stock = $this->getStockService()->fetch($stockId);
        $this->getInvalidator()->queueCacheInvalidations($stockLocation, $stock);
        $this->getListingStatusService()->updateRelatedListings($stock);
        return $stockLocationHal;
    }

    protected function setStockService($stockService)
    {
        $this->stockService = $stockService;
        return $this;
    }

    protected function getStockService()
    {
        return $this->stockService;
    }

    protected function setListingStatusService($listingStatusService)
    {
        $this->listingStatusService = $listingStatusService;
        return $this;
    }

    protected function getListingStatusService()
    {
        return $this->listingStatusService;
    }

    protected function setStockLocationMapper($stockLocationMapper)
    {
        $this->stockLocationMapper = $stockLocationMapper;
        return $this;
    }

    protected function getStockLocationMapper()
    {
        return $this->stockLocationMapper;
    }

    /**
     * @return self
     */
    public function setInvalidator(Invalidator $invalidator)
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
