<?php
namespace CG\Stock\Command;

use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\PurchaseOrder\Collection as PurchaseOrderCollection;
use CG\PurchaseOrder\Entity as PurchaseOrder;
use CG\PurchaseOrder\Filter as PurchaseOrderFilter;
use CG\PurchaseOrder\Item\Collection as PurchaseOrderItemCollection;
use CG\PurchaseOrder\Item\Entity as PurchaseOrderItem;
use CG\PurchaseOrder\Item\Filter as PurchaseOrderItemFilter;
use CG\PurchaseOrder\Item\Service as PurchaseOrderItemService;
use CG\PurchaseOrder\Service as PurchaseOrderService;
use CG\PurchaseOrder\Status as PurchaseOrderStatus;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stock\Collection as StockCollection;
use CG\Stock\Entity as Stock;
use CG\Stock\Filter as StockFilter;
use CG\Stock\Gearman\Generator\AdjustOnPurchaseOrder as AdjustOnPurchaseOrderGenerator;
use CG\Stock\Service as StockService;

class SetOnPurchaseOrderCounts implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'SetOnPurchaseOrderCountsCommand';

    /** @var OrganisationUnitService */
    protected $organisationUnitService;
    /** @var PurchaseOrderService */
    protected $purchaseOrderService;
    /** @var PurchaseOrderItemService */
    protected $purchaseOrderItemService;
    /** @var StockService */
    protected $stockService;
    /** @var AdjustOnPurchaseOrderGenerator */
    protected $adjustOnPurchaseOrderGenerator;

    public function __construct(
        OrganisationUnitService $organisationUnitService,
        PurchaseOrderService $purchaseOrderService,
        PurchaseOrderItemService $purchaseOrderItemService,
        StockService $stockService,
        AdjustOnPurchaseOrderGenerator $adjustOnPurchaseOrderGenerator
    ) {
        $this->organisationUnitService = $organisationUnitService;
        $this->purchaseOrderService = $purchaseOrderService;
        $this->purchaseOrderItemService = $purchaseOrderItemService;
        $this->stockService = $stockService;
        $this->adjustOnPurchaseOrderGenerator = $adjustOnPurchaseOrderGenerator;
    }

    public function __invoke(bool $dryRun = true): int
    {
        $this->logDebug('SetOnPurchaseOrderCounts command invoked %s', [($dryRun ? 'DRY RUN' : '')], [static::LOG_CODE, 'Invoked']);
        $updatedCount = 0;

        $rootOus = $this->organisationUnitService->fetchRootOus('all', 1);
        foreach ($rootOus as $rootOu) {
            $this->addGlobalLogEventParams(['ou' => $rootOu->getId(), 'rootOu' => $rootOu->getId()]);
            $updatedCount += $this->setOnPurchaseOrderQuantitiesForOu($rootOu, $dryRun);
        }
        $this->removeGlobalLogEventParams(['ou', 'rootOu']);

        $this->logDebug('SetOnPurchaseOrderCounts command finished %s- affected %d stock entries', [($dryRun ? 'DRY RUN ' : ''), $updatedCount], [static::LOG_CODE, 'Done']);
        return $updatedCount;
    }

    protected function setOnPurchaseOrderQuantitiesForOu(OrganisationUnit $rootOu, bool $dryRun): int
    {
        $purchaseOrders = $this->fetchOpenPurchaseOrdersForOu($rootOu);
        if (!$purchaseOrders) {
            $this->logDebug('No open Purchase Orders for OU %d', [$rootOu->getId()], [static::LOG_CODE, 'NoOpenPOs']);
            return 0;
        }
        $this->logDebug('Found %d open Purchase Orders for OU %d', [$purchaseOrders->count(), $rootOu->getId()], [static::LOG_CODE, 'Count']);
        // Because we're within the API the items are not embedded on the PO entities
        $purchaseOrderItems = $this->fetchItemsForPurchaseOrders($purchaseOrders);
        $quantities = $this->getOnPurchaseOrderQuantitiesBySku($purchaseOrderItems);
        return $this->setOnPurchaseOrderQuantitiesBySku($quantities, $rootOu, $dryRun);
    }

    protected function fetchOpenPurchaseOrdersForOu(OrganisationUnit $rootOu): ?PurchaseOrderCollection
    {
        try {
            $filter = $this->buildOpenPurchaseOrderFilter($rootOu);
            return $this->purchaseOrderService->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            return null;
        }
    }

    protected function buildOpenPurchaseOrderFilter(OrganisationUnit $rootOu): PurchaseOrderFilter
    {
        $statuses = PurchaseOrderStatus::getAllStatuses();
        unset($statuses[PurchaseOrderStatus::COMPLETE]);
        return (new PurchaseOrderFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId([$rootOu->getId()])
            ->setStatus($statuses);
    }

    protected function fetchItemsForPurchaseOrders(PurchaseOrderCollection $purchaseOrders): PurchaseOrderItemCollection
    {
        try {
            $filter = $this->buildPurchaseOrderItemFilter($purchaseOrders);
            return $this->purchaseOrderItemService->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            $this->logWarning('There are open Purchase Orders for OU %d but none of them have items, this is unexpected.', [$purchaseOrders->getFirst()->getOrganisationUnitId()], [static::LOG_CODE, 'NoItems']);
            return new PurchaseOrderItemCollection(PurchaseOrderItem::class, __FUNCTION__);
        }
    }

    protected function buildPurchaseOrderItemFilter(PurchaseOrderCollection $purchaseOrders): PurchaseOrderItemFilter
    {
        return (new PurchaseOrderItemFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setPurchaseOrderId($purchaseOrders->getIds());
    }

    protected function getOnPurchaseOrderQuantitiesBySku(PurchaseOrderItemCollection $purchaseOrderItems): array
    {
        $quantities = [];
        /** @var PurchaseOrderItem $item */
        foreach ($purchaseOrderItems as $item) {
            if (!isset($quantities[$item->getSku()])) {
                $quantities[$item->getSku()] = 0;
            }
            $quantities[$item->getSku()] += $item->getQuantity();
        }
        return $quantities;
    }

    protected function setOnPurchaseOrderQuantitiesBySku(array $quantitiesBySku, OrganisationUnit $rootOu, bool $dryRun): int
    {
        $updatedCount = 0;
        if (empty($quantitiesBySku)) {
            return $updatedCount;
        }
        $stocks = $this->fetchStockBySkus(array_keys($quantitiesBySku), $rootOu);
        foreach ($stocks as $stock) {
            $quantity = $quantitiesBySku[$stock->getSku()];
            $stockLocation = $stock->getLocations()->getFirst();
            $adjustment = $quantity - $stockLocation->getOnPurchaseOrder();
            if ($adjustment == 0) {
                continue;
            }
            $this->adjustOnPurchaseOrderQuantity($rootOu, $stock, $adjustment, $dryRun);
            $updatedCount++;
        }
        return $updatedCount;
    }

    protected function fetchStockBySkus(array $skus, OrganisationUnit $rootOu): StockCollection
    {
        $filter = (new StockFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setSku($skus)
            ->setOrganisationUnitId([$rootOu->getId()]);
        return $this->stockService->fetchCollectionByFilter($filter);
    }

    protected function adjustOnPurchaseOrderQuantity(OrganisationUnit $rootOu, Stock $stock, int $adjustment, bool $dryRun): void
    {
        if ($dryRun) {
            $this->logDebug('Dry run, would have adjusted OU %d, SKU %s onPurchaseOrder quantity by %d', [$rootOu->getId(), $stock->getSku(), $adjustment], [static::LOG_CODE, 'Adjustment']);
            return;
        }
        $this->logDebug('Adjusting OU %d, SKU %s onPurchaseOrder quantity by %d', [$rootOu->getId(), $stock->getSku(), $adjustment], [static::LOG_CODE, 'Adjustment']);
        $this->adjustOnPurchaseOrderGenerator->createJobFromRawData($rootOu->getId(), $stock->getSku(), $adjustment);
    }
}