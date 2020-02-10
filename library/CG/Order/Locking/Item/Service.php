<?php
namespace CG\Order\Locking\Item;

use CG\Account\Client\Service as AccountService;
use CG\CGLib\Nginx\Cache\Invalidator\OrderItems as Invalidator;
use CG\Locking\Service as LockingService;
use CG\Notification\Gearman\Generator\Dispatcher as Notifier;
use CG\Order\Client\Gearman\Generator\AutoEmailInvoice as AutoEmailInvoiceGenerator;
use CG\Order\Client\Gearman\Generator\CalculateOrderWeight as CalculateOrderWeightGenerator;
use CG\Order\Client\Gearman\Generator\LinkMatchingOrders as LinkMatchingOrdersGenerator;
use CG\Order\Service\Item\Fee\Service as FeeService;
use CG\Order\Service\Item\GiftWrap\Service as GiftWrapService;
use CG\Order\Service\Item\InvalidationService as ItemService;
use CG\Order\Service\Item\Transaction\UpdateItemAndStockFactory as TransactionFactory;
use CG\Order\Shared\Item\Mapper as ItemMapper;
use CG\Order\Shared\Item\StorageInterface as ItemStorage;
use CG\Order\Shared\Status as OrderStatus;
use CG\OrganisationUnit\Service as OUService;
use CG\Stock\AdjustmentCalculator as StockAdjustmentCalculator;
use CG\Stock\Auditor as StockAuditor;
use CG\Stock\Location\AdjustmentDecider as StockLocationDecider;
use CG\Stock\Service as StockService;
use GearmanClient;
use Zend\EventManager\GlobalEventManager;

class Service extends ItemService
{
    /** @var LockingService $lockingService */
    protected $lockingService;

    public function __construct(
        ItemStorage $repository,
        ItemMapper $mapper,
        FeeService $feeService,
        GiftWrapService $giftWrapService,
        OrderStatus $orderStatus,
        StockService $stockService,
        StockAdjustmentCalculator $stockAdjustmentCalculator,
        StockLocationDecider $stockLocationDecider,
        TransactionFactory $transactionFactory,
        GlobalEventManager $eventManager,
        OUService $ouService,
        StockAuditor $stockAuditor,
        AccountService $accountService,
        Notifier $notifier,
        GearmanClient $gearmanClient,
        LinkMatchingOrdersGenerator $linkMatchingOrdersGenerator,
        AutoEmailInvoiceGenerator $autoEmailInvoiceGenerator,
        CalculateOrderWeightGenerator $calculateOrderWeightGenerator,
        Invalidator $invalidator,
        LockingService $lockingService,
        GearmanClient $orderGearmanClient
    ) {
        parent::__construct(
            $repository,
            $mapper->setMapToEntityClass(Entity::class),
            $feeService,
            $giftWrapService,
            $orderStatus,
            $stockService,
            $stockAdjustmentCalculator,
            $stockLocationDecider,
            $transactionFactory,
            $eventManager,
            $ouService,
            $stockAuditor,
            $accountService,
            $notifier,
            $gearmanClient,
            $linkMatchingOrdersGenerator,
            $autoEmailInvoiceGenerator,
            $calculateOrderWeightGenerator,
            $invalidator,
            $orderGearmanClient
        );
        $this->setLockingService($lockingService);
    }

    /**
     * @inheritDoc
     * @param Entity $entity
     */
    public function save($entity)
    {
        $lock = $this->lockingService->lock($entity);
        try {
            $entity = parent::save($entity);
            $this->lockingService->unlock($lock);
            return $entity;
        } catch (\Exception $exception) {
            $this->lockingService->unlock($lock);
            throw $exception;
        }
    }

    /**
     * @return self
     */
    protected function setLockingService(LockingService $lockingService)
    {
        $this->lockingService = $lockingService;
        return $this;
    }

}
