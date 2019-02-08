<?php
namespace CG\Stock\Location\Service;

use CG\Account\Client\Service as AccountService;
use CG\CGLib\Gearman\Generator\UpdateRelatedListingsForStock;
use CG\CGLib\Nginx\Cache\Invalidator\ProductStock as NginxCacheInvalidator;
use CG\Notification\Gearman\Generator\Dispatcher as Notifier;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Product\LinkRelated\Entity as ProductLinkRelated;
use CG\Product\LinkRelated\StorageInterface as ProductLinkRelatedStorage;
use CG\Product\StockMode;
use CG\Slim\Renderer\ResponseType\Hal;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Auditor;
use CG\Stock\Collection as StockCollection;
use CG\Stock\Entity as Stock;
use CG\Stock\Filter as StockFilter;
use CG\Stock\Location\Collection as StockLocationCollection;
use CG\Stock\Location\Entity as StockLocation;
use CG\Stock\Location\Filter as StockLocationFilter;
use CG\Stock\Location\Mapper as LocationMapper;
use CG\Stock\Location\Service as BaseService;
use CG\Stock\Location\Storage\Cache as StockLocationCache;
use CG\Stock\Location\StorageInterface as LocationStorage;
use CG\Stock\StorageInterface as StockStorage;
use CG\Order\Client\Gearman\Generator\DetermineDispatchableOrders as DetermineDispatchableOrdersGenerator;

class Service extends BaseService implements StatsAwareInterface
{
    use StatsTrait;

    const LOG_CODE_OVERSELL = 'Stock oversell alert';
    const LOG_MSG_OVERSELL = '"%s" for ou %d has oversold at location %s';
    const LOG_CODE = 'LocationServiceService';

    const STATS_OVERSELL = 'stock.oversell.ou-%d';

    /** @var OrganisationUnitService $organisationUnitService */
    protected $organisationUnitService;
    /** @var AccountService $accountService */
    protected $accountService;
    /* @var ProductLinkRelatedStorage */
    protected $productLinkRelatedStorage;
    /** @var StockLocationCache $stockLocationCache */
    protected $stockLocationCache;
    /** @var NginxCacheInvalidator $nginxCacheInvalidator */
    protected $nginxCacheInvalidator;
    /** @var UpdateRelatedListingsForStock */
    protected $updateRelatedListingsForStockGenerator;
    /** @var DetermineDispatchableOrdersGenerator */
    protected $determineDispatchableOrdersJobGenerator;

    public function __construct(
        LocationStorage $repository,
        LocationMapper $mapper,
        Auditor $auditor,
        StockStorage $stockStorage,
        Notifier $notifier,
        OrganisationUnitService $organisationUnitService,
        AccountService $accountService,
        ProductLinkRelatedStorage $productLinkRelatedStorage,
        StockLocationCache $stockLocationCache,
        NginxCacheInvalidator $nginxCacheInvalidator,
        UpdateRelatedListingsForStock $updateRelatedListingsForStockGenerator,
        DetermineDispatchableOrdersGenerator $determineDispatchableOrdersJobGenerator
    ) {
        parent::__construct($repository, $mapper, $auditor, $stockStorage, $notifier);
        $this->organisationUnitService = $organisationUnitService;
        $this->accountService = $accountService;
        $this->productLinkRelatedStorage = $productLinkRelatedStorage;
        $this->stockLocationCache = $stockLocationCache;
        $this->nginxCacheInvalidator = $nginxCacheInvalidator;
        $this->updateRelatedListingsForStockGenerator = $updateRelatedListingsForStockGenerator;
        $this->determineDispatchableOrdersJobGenerator = $determineDispatchableOrdersJobGenerator;
    }

    public function save($stockLocation, array $adjustmentIds = []): Hal
    {
        try {
            /** @var Stock $stock */
            $stock = $this->stockStorage->fetch($stockLocation->getStockId());
        } catch (NotFound $exception) {
            return parent::save($stockLocation, $adjustmentIds);
        }

        try {
            /** @var StockLocation $currentStockLocation */
            $currentStockLocation = $this->fetch($stockLocation->getId());
            $this->handleOversell($stock, $currentStockLocation, $stockLocation);
        } catch (NotFound $exception) {
            // Saving new entity - nothing to do
        }

        $relatedStockLocations = $this->fetchRelatedStockLocations($stock);
        $relatedStocks = $this->fetchRelatedStock($relatedStockLocations);
        $stockLocationHal = parent::save($stockLocation, $adjustmentIds);
        $this->updateRelated($stock, $stockLocation, $relatedStocks, $relatedStockLocations);
        $this->generateDetermineDispatchableOrdersJob($stock);
        return $stockLocationHal;
    }

    protected function fetchRelatedStockLocations(Stock $stock): StockLocationCollection
    {
        try {
            $productLinkRelatedId = ProductLinkRelated::generateId($stock->getOrganisationUnitId(), $stock->getSku());
            /** @var $productLinkRelated \CG\Product\LinkRelated\Entity */
            $productLinkRelated = $this->productLinkRelatedStorage->fetch($productLinkRelatedId);
            $productSkus = $productLinkRelated->getRelatedSkusMap();
        } catch (NotFound $e) {
            $productSkus = [];
        }

        $this->logDebugDump($productSkus, 'Found related product skus to %s for ouId %d', [$stock->getSku(), $stock->getOrganisationUnitId()], static::LOG_CODE);

        $filter = (new StockLocationFilter('all', 1))->setOuIdSku(array_map(
            function ($sku) use ($stock) {
                return ProductLinkRelated::generateId($stock->getOrganisationUnitId(), $sku);
            },
            $productSkus
        ));

        try {
            if (empty($filter->getOuIdSku())) {
                throw new NotFound('No related stock locations');
            }
            return $this->fetchCollectionByFilter($filter);
        } catch (NotFound $exception) {
            return new StockLocationCollection(StockLocation::class, 'fetchCollectionByFilter', $filter->toArray());
        }
    }

    protected function fetchRelatedStock(StockLocationCollection $relatedStockLocations): StockCollection
    {
        $filter = (new StockFilter('all', 1))->setId($relatedStockLocations->getArrayOf('stockId'));
        try {
            if (empty($filter->getId())) {
                throw new NotFound('No related stock');
            }
            return $this->stockStorage->fetchCollectionByFilter($filter);
        } catch (NotFound $exception) {
            return new StockCollection(Stock::class, 'fetchCollectionByFilter', $filter->toArray());
        }
    }

    protected function handleOversell(Stock $stock, StockLocation $current, StockLocation $updated): Service
    {
        $organisationUnitIds = $this->organisationUnitService->fetchRelatedOrganisationUnitIds($stock->getOrganisationUnitId());
        if (
            $current->getAvailable() >= 0
            && $updated->getAvailable() < 0
            && $this->accountService->isStockManagementEnabled($organisationUnitIds)
            && $stock->getStockMode() != StockMode::LIST_FIXED
        ) {
            $this->logNotice(static::LOG_MSG_OVERSELL, [$stock->getSku(), $stock->getOrganisationUnitId(), $updated->getId()], static::LOG_CODE_OVERSELL);
            $this->statsIncrement(static::STATS_OVERSELL, [$stock->getOrganisationUnitId()]);
        }
        return $this;
    }

    protected function updateRelated(Stock $stock, StockLocation $stockLocation, StockCollection $relatedStocks, StockLocationCollection $relatedStockLocations)
    {
        /** @var StockLocation $relatedStockLocation */
        foreach ($relatedStockLocations as $relatedStockLocation) {
            $this->stockLocationCache->remove($relatedStockLocation);

            $relatedStock = $relatedStocks->getById($relatedStockLocation->getStockId());
            if ($relatedStock instanceof Stock) {
                $this->nginxCacheInvalidator->invalidateProductsForStockLocation($relatedStockLocation, $relatedStock);
            }
        }

        try {
            $stockIds = $relatedStockLocations->getArrayOf('stockId');
            $locationIds = $relatedStockLocations->getArrayOf('locationId');
            if (empty($stockIds) || empty($locationIds)) {
                throw new NotFound('No related stock locations to update');
            }

            /** @var StockLocationCollection $updatedStockLocations */
            $updatedStockLocations = $this->fetchCollectionByFilter(
                (new StockLocationFilter('all', 1))->setStockId($stockIds)->setLocationId($locationIds)
            );

            /** @var StockLocation $updatedStockLocation */
            foreach ($updatedStockLocations as $updatedStockLocation) {
                $relatedStockLocation = $relatedStockLocations->getById($updatedStockLocation->getId());
                if (
                    !($relatedStockLocation instanceof StockLocation)
                    || $relatedStockLocation->getETag() == $updatedStockLocation->getETag()
                ) {
                    continue;
                }

                $relatedStock = $relatedStocks->getById($relatedStockLocation->getStockId());
                if (!($relatedStock instanceof Stock)) {
                    continue;
                }

                if ($stockLocation->getId() != $relatedStockLocation->getId()) {
                    $this->auditor->auditStockLocationChange($updatedStockLocation, $relatedStock);
                }

                $this->updateRelatedListings($relatedStock);
            }
        } catch (NotFound $exception) {
            // No related stock locations to update
        } finally {
            $this->updateRelatedListings($stock);
        }
    }

    protected function updateRelatedListings(Stock $stock): Service
    {
        $this->updateRelatedListingsForStockGenerator->generateJob($stock);
        return $this;
    }

    protected function generateDetermineDispatchableOrdersJob(Stock $stock)
    {
        $this->determineDispatchableOrdersJobGenerator->generateJobForRootOuIdAndSku($stock->getOrganisationUnitId(), $stock->getSku());
    }
} 
