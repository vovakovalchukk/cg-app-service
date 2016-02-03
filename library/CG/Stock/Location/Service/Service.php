<?php
namespace CG\Stock\Location\Service;

use CG\Notification\Gearman\Generator\Dispatcher as Notifier;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Auditor;
use CG\Stock\Location\Mapper as LocationMapper;
use CG\Stock\Location\Service as BaseService;
use CG\Stock\Location\Entity as StockLocation;
use CG\Stock\Entity as Stock;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Account\Client\Service as AccountService;
use CG\Stock\Location\StorageInterface as LocationStorage;
use CG\Stock\StorageInterface as StockStorage;

/**
 * @property StockStorage $stockStorage
 */
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

    public function __construct(
        LocationStorage $repository,
        LocationMapper $mapper,
        Auditor $auditor,
        StockStorage $stockStorage,
        Notifier $notifier,
        OrganisationUnitService $organisationUnitService,
        AccountService $accountService
    ) {
        parent::__construct($repository, $mapper, $auditor, $stockStorage, $notifier);
        $this->setOrganisationUnitService($organisationUnitService)->setAccountService($accountService);
    }


    /**
     * @param StockLocation $entity
     */
    public function save($entity, array $adjustmentIds = [])
    {
        try {
            /** @var Stock $stock */
            $stock = $this->stockStorage->fetch($entity->getStockId());
        } catch (NotFound $exception) {
            return parent::save($entity);
        }

        try {
            /** @var StockLocation $current */
            $current = $this->fetch($entity->getId());
            $this->handleOversell($stock, $current, $entity);
        } catch (NotFound $exception) {
            // Saving new entity - nothing to do
        }

        return parent::save($entity);
    }

    /**
     * @return self
     */
    protected function handleOversell(Stock $stock, StockLocation $current, StockLocation $entity)
    {
        $organisationUnitIds = $this->organisationUnitService->fetchRelatedOrganisationUnitIds($stock->getOrganisationUnitId());
        if (
            $this->accountService->isStockManagementEnabled($organisationUnitIds)
            && $current->getAvailable() >= 0
            && $entity->getAvailable() < 0
        ) {
            $this->logNotice(static::LOG_MSG_OVERSELL, [$stock->getSku(), $stock->getOrganisationUnitId(), $entity->getId()], static::LOG_CODE_OVERSELL);
            $this->statsIncrement(static::STATS_OVERSELL, [$stock->getOrganisationUnitId()]);
        }
        return $this;
    }

    /**
     * @return self
     */
    protected function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }
} 