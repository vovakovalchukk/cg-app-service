<?php
namespace CG\Controllers\Stock\Location;

use CG\Listing\ListingStatusService;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stock\Location\Mapper as StockLocationMapper;
use CG\Stock\Location\Service;
use CG\Stock\Service as StockService;
use CG\Slim\ControllerTrait;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\Controller\Entity\DeleteTrait;
use Nocarrier\Hal;
use Slim\Slim;
use Zend\Di\Di;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class Location implements LoggerAwareInterface
{
    use ControllerTrait, GetTrait, PutTrait, DeleteTrait;
    use LogTrait;

    protected $listingStatusService;
    protected $organisationUnitService;
    protected $stockService;
    protected $stockLocationMapper;

    public function __construct(
        ListingStatusService $listingStatusService,
        OrganisationUnitService $organisationUnitservice,
        StockLocationMapper $stockLocationMapper,
        Slim $app,
        Service $service,
        Di $di,
        StockService $stockService
    ) {
        $this->setListingStatusService($listingStatusService)
            ->setOrganisationUnitService($organisationUnitService)
            ->setStockLocationMapper($stockLocationMapper)
            ->setSlim($app)
            ->setService($service)
            ->setStockService($stockService)
            ->setDi($di);
    }

    public function put($id, Hal $hal)
    {
        $stockLocation = $this->getService()->saveHal($hal, ["id" => $id]);
        $stockId = $this->getStockLocationMapper()->fromHal($stockLocation)->getStockId();
        $stock = $this->getStockService()->fetch($stockId);
        $rootOUID = $stock->getOrganisationUnitId();
        $relatedOrganisationUnits = $this->getOrganisationUnitService()->fetchFiltered($limit, $page, $rootOUID);
        $relatedOUIDs = [];
        foreach($relatedOrganisationUnits as $relatedOU) {
            $relatedOUIDs[] = $relatedOU->getId();
        }
        $relatedOUIDs[] = $rootOUID;
        $this->getListingStatusService()->updateRelatedListings($stock, $relatedOUIDs);
        
        return $stockLocation;
    }

    protected function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }

    public function getOrganisationUnitService()
    {
        return $this->organisationUnitService;
    }

    protected function setStockService($stockService)
    {
        $this->stockService = $stockService;
        return $this;
    }

    public function getStockService()
    {
        return $this->stockService;
    }

    protected function setListingStatusService($listingStatusService)
    {
        $this->listingStatusService = $listingStatusService;
        return $this;
    }

    public function getListingStatusService()
    {
        return $this->listingStatusService;
    }

    protected function setStockLocationMapper($stockLocationMapper)
    {
        $this->stockLocationMapper = $stockLocationMapper;
        return $this;
    }

    public function getStockLocationMapper()
    {
        return $this->stockLocationMapper;
    }
}