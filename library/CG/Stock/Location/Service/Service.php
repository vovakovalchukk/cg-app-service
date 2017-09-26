<?php
namespace CG\Stock\Location\Service;

use CG\Account\Client\Service as AccountService;
use CG\CGLib\Gearman\Generator\UpdateRelatedListingsForStock;
use CG\Notification\Gearman\Generator\Dispatcher as Notifier;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Product\Service\Service as ProductService;
use CG\Product\StockMode;
use CG\Slim\Renderer\ResponseType\Hal;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Auditor;
use CG\Stock\Collection as StockCollection;
use CG\Stock\Entity as Stock;
use CG\Stock\Location\Entity as StockLocation;
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
    /** @var ProductService $productService */
    protected $productService;
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
        ProductService $productService,
        UpdateRelatedListingsForStock $updateRelatedListingsForStockGenerator
    ) {
        parent::__construct($repository, $mapper, $auditor, $stockStorage, $notifier);
        $this->organisationUnitService = $organisationUnitService;
        $this->accountService = $accountService;
        $this->productService = $productService;
        $this->updateRelatedListingsForStockGenerator = $updateRelatedListingsForStockGenerator;
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

        $stockLocationHal = parent::save($stockLocation, $adjustmentIds);
        $this->updateRelatedListings($stock);
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

    protected function updateRelatedListings(Stock $stock): Service
    {
        $this->updateRelatedListingsForStockGenerator->generateJob($stock);
        return $this;
    }
} 
