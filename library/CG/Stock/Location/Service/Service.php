<?php
namespace CG\Stock\Location\Service;

use CG\Account\Client\Service as AccountService;
use CG\CGLib\Gearman\Generator\UpdateRelatedListingsForStock;
use CG\CGLib\Nginx\Cache\Invalidator\ProductStock as NginxCacheInvalidator;
use CG\Notification\Gearman\Generator\Dispatcher as Notifier;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Product\LinkNode\Entity as ProductLinkNode;
use CG\Product\LinkNode\StorageInterface as ProductLinkNodeStorage;
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

class Service extends BaseService implements StatsAwareInterface
{
    use StatsTrait;

    const LOG_CODE_OVERSELL = 'Stock oversell alert';
    const LOG_MSG_OVERSELL = '"%s" for ou %d has oversold at location %s';

    const STATS_OVERSELL = 'stock.oversell.ou-%d';

    /** @var OrganisationUnitService $organisationUnitService */
    protected $organisationUnitService;
    /** @var AccountService $accountService */
    protected $accountService;
    /** @var ProductLinkNodeStorage $productLinkNodeStorage */
    protected $productLinkNodeStorage;
    /** @var StockLocationCache $stockLocationCache */
    protected $stockLocationCache;
    /** @var NginxCacheInvalidator $nginxCacheInvalidator */
    protected $nginxCacheInvalidator;
    /** @var UpdateRelatedListingsForStock */
    protected $updateRelatedListingsForStockGenerator;

    public function __construct(
        LocationStorage $repository,
        LocationMapper $mapper,
        Auditor $auditor,
        StockStorage $stockStorage,
        Notifier $notifier,
        OrganisationUnitService $organisationUnitService,
        AccountService $accountService,
        ProductLinkNodeStorage $productLinkNodeStorage,
        StockLocationCache $stockLocationCache,
        NginxCacheInvalidator $nginxCacheInvalidator,
        UpdateRelatedListingsForStock $updateRelatedListingsForStockGenerator
    ) {
        parent::__construct($repository, $mapper, $auditor, $stockStorage, $notifier);
        $this->organisationUnitService = $organisationUnitService;
        $this->accountService = $accountService;
        $this->productLinkNodeStorage = $productLinkNodeStorage;
        $this->stockLocationCache = $stockLocationCache;
        $this->nginxCacheInvalidator = $nginxCacheInvalidator;
        $this->updateRelatedListingsForStockGenerator = $updateRelatedListingsForStockGenerator;
    }

    /**
     * @param StockLocation $stockLocation
     */
    public function save($stockLocation, array $adjustmentIds = []): Hal
    {

        $this->logDebugDump($stockLocation, 'Stock Location is ', [], 'MYTEST');

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
        $this->updateRelated($stock, $relatedStocks, $relatedStockLocations);
        return $stockLocationHal;
    }

    protected function getRelatedSkus(Stock $stock): array
    {
        $sku = $stock->getSku();

        $productSkus = [];

        while ($sku != null) {
            try {
                /** @var ProductLinkNode $productLinkNode */
                $productLinkNode = $this->productLinkNodeStorage->fetch(
                    ProductLinkNode::generateId($stock->getOrganisationUnitId(), $sku)
                );
            } catch (NotFound $exception) {
                $productLinkNode = new ProductLinkNode($stock->getOrganisationUnitId(), $sku, [], []);
            }

            $this->logDebugDump($productLinkNode, 'LINKED NODE', [], 'MYTEST');

            if (empty($productLinkNode->getDescendants())) {
                $sku = null;
                break;
            }

            

            $descendantsCount = count($productLinkNode->getDescendants());

            $sku = $productLinkNode->getDescendants()[$descendantsCount-1];
        }

        return $productSkus;
    }

    protected function fetchRelatedStockLocations(Stock $stock): StockLocationCollection
    {




        $filter = (new StockLocationFilter('all', 1))->setOuIdSku(array_map(
            function ($sku) use ($productLinkNode) {
                return ProductLinkNode::generateId($productLinkNode->getOrganisationUnitId(), $sku);
            },
            iterator_to_array($productLinkNode)
        ));

        $this->logDebugDump($filter, 'LINKED NODE FILTER', [], 'MYTEST');

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

    protected function updateRelated(Stock $stock, StockCollection $relatedStocks, StockLocationCollection $relatedStockLocations)
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
                throw new NotFound('No related stock loations to update');
            }

            $this->logDebugDump($stockIds, 'Stock IDS', [], 'MYTEST');
            $this->logDebugDump($locationIds, 'Location IDS', [], 'MYTEST');

            /** @var StockLocationCollection $updatedStockLocations */
            $updatedStockLocations = $this->fetchCollectionByFilter(
                (new StockLocationFilter('all', 1))->setStockId($stockIds)->setLocationId($locationIds)
            );

            $this->logDebugDump($updatedStockLocations, 'Updated Stock Locations', [], 'MYTEST');

            /** @var StockLocation $updatedStockLocation */
            foreach ($updatedStockLocations as $updatedStockLocation) {
                $stockLocation = $relatedStockLocations->getById($updatedStockLocation->getId());
                if (
                    !($stockLocation instanceof StockLocation)
                    || $stockLocation->getETag() == $updatedStockLocation->getETag()
                ) {
                    continue;
                }

                $relatedStock = $relatedStocks->getById($stockLocation->getStockId());
                if ($relatedStock instanceof Stock) {
                    $this->updateRelatedListings($relatedStock);
                }
            }
        } catch (NotFound $exception) {
            // No related stock loations to update
        } finally {
            $this->updateRelatedListings($stock);
        }
    }

    protected function updateRelatedListings(Stock $stock): Service
    {
        $this->updateRelatedListingsForStockGenerator->generateJob($stock);
        return $this;
    }
} 
