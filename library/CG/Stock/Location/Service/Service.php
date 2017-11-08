<?php
namespace CG\Stock\Location\Service;

use CG\Account\Client\Service as AccountService;
use CG\CGLib\Gearman\Generator\UpdateRelatedListingsForStock;
use CG\CGLib\Nginx\Cache\Invalidator\ProductStock as NginxCacheInvalidator;
use CG\FeatureFlags\Feature;
use CG\FeatureFlags\Lookup\Service as FeatureFlagsService;
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
use CG\Stock\Location\Entity as StockLocation;
use CG\Stock\Location\Filter as StockLocationFilter;
use CG\Stock\Location\Mapper as LocationMapper;
use CG\Stock\Location\Service as BaseService;
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
    /** @var NginxCacheInvalidator $nginxCacheInvalidator */
    protected $nginxCacheInvalidator;
    /** @var UpdateRelatedListingsForStock */
    protected $updateRelatedListingsForStockGenerator;
    /** @var FeatureFlagsService $featureFlagsService */
    protected $featureFlagsService;

    public function __construct(
        LocationStorage $repository,
        LocationMapper $mapper,
        Auditor $auditor,
        StockStorage $stockStorage,
        Notifier $notifier,
        OrganisationUnitService $organisationUnitService,
        AccountService $accountService,
        ProductLinkNodeStorage $productLinkNodeStorage,
        NginxCacheInvalidator $nginxCacheInvalidator,
        UpdateRelatedListingsForStock $updateRelatedListingsForStockGenerator,
        FeatureFlagsService $featureFlagsService
    ) {
        parent::__construct($repository, $mapper, $auditor, $stockStorage, $notifier);
        $this->organisationUnitService = $organisationUnitService;
        $this->accountService = $accountService;
        $this->productLinkNodeStorage = $productLinkNodeStorage;
        $this->nginxCacheInvalidator = $nginxCacheInvalidator;
        $this->updateRelatedListingsForStockGenerator = $updateRelatedListingsForStockGenerator;
        $this->featureFlagsService = $featureFlagsService;
    }

    /**
     * @param StockLocation $stockLocation
     */
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

        $stockLocationHal = parent::save($stockLocation, $adjustmentIds);
        $this->updateRelated($stock);
        return $stockLocationHal;
    }

    protected function handleOversell(Stock $stock, StockLocation $current, StockLocation $entity): Service
    {
        $organisationUnitIds = $this->organisationUnitService->fetchRelatedOrganisationUnitIds($stock->getOrganisationUnitId());
        if (
            $current->getAvailable() >= 0
            && $entity->getAvailable() < 0
            && $this->accountService->isStockManagementEnabled($organisationUnitIds)
            && $stock->getStockMode() != StockMode::LIST_FIXED
        ) {
            $this->logNotice(static::LOG_MSG_OVERSELL, [$stock->getSku(), $stock->getOrganisationUnitId(), $entity->getId()], static::LOG_CODE_OVERSELL);
            $this->statsIncrement(static::STATS_OVERSELL, [$stock->getOrganisationUnitId()]);
        }
        return $this;
    }

    protected function updateRelated(Stock $stock)
    {
        try {
            if (!$this->featureFlagsService->featureEnabledForEntity(Feature::LINKED_PRODUCTS, $stock)) {
                return;
            }

            try {
                /** @var ProductLinkNode $productLinkNode */
                $productLinkNode = $this->productLinkNodeStorage->fetch(
                    ProductLinkNode::generateId($stock->getOrganisationUnitId(), $stock->getSku())
                );
                $relatedStockLocations = $this->fetchCollectionByFilter(
                    (new StockLocationFilter('all', 1))->setOuIdSku(array_map(
                        function ($sku) use ($productLinkNode) {
                            return ProductLinkNode::generateId($productLinkNode->getOrganisationUnitId(), $sku);
                        },
                        iterator_to_array($productLinkNode)
                    ))
                );
                /** @var StockCollection $relatedStocks */
                $relatedStocks = $this->stockStorage->fetchCollectionByFilter(
                    (new StockFilter('all', 1))->setId($relatedStockLocations->getArrayOf('stockId'))
                );
            } catch (NotFound $exception) {
                return;
            }

            /** @var StockLocation $relatedStockLocation */
            foreach ($relatedStockLocations as $relatedStockLocation) {
                $relatedStock = $relatedStocks->getById($relatedStockLocation->getStockId());
                if (!($relatedStock instanceof Stock)) {
                    continue;
                }
                $this->nginxCacheInvalidator->invalidateProductsForStockLocation($relatedStockLocation, $relatedStock);
            }

            foreach ($relatedStocks as $relatedStock) {
                $this->updateRelatedListings($relatedStock);
            }
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
