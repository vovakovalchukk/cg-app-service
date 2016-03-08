<?php
namespace CG\Order\Service\Item;

use CG\Account\Client\Service as AccountService;
use CG\CGLib\Nginx\Cache\Invalidator\OrderItems as Invalidator;
use CG\Notification\Gearman\Generator\Dispatcher as Notifier;
use CG\Order\Service\InvoiceEmailer\Service as InvoiceEmailer;
use CG\Order\Service\Item\Fee\Service as FeeService;
use CG\Order\Service\Item\GiftWrap\Service as GiftWrapService;
use CG\Order\Service\Item\Service as ItemService;
use CG\Order\Service\Item\Transaction\UpdateItemAndStockFactory as TransactionFactory;
use CG\Order\Shared\Item\Entity as ItemEntity;
use CG\Order\Shared\Item\Mapper as ItemMapper;
use CG\Order\Shared\Item\StorageInterface as ItemStorage;
use CG\Order\Shared\Status as OrderStatus;
use CG\OrganisationUnit\Service as OUService;
use CG\Stock\AdjustmentCalculator as StockAdjustmentCalculator;
use CG\Stock\Auditor as StockAuditor;
use CG\Stock\Location\AdjustmentDecider as StockLocationDecider;
use CG\Stock\Service as StockService;
use Exception;
use Zend\EventManager\GlobalEventManager;
use GearmanClient;

class InvalidationService extends ItemService
{
    /** @var Invalidator $invalidator */
    protected $invalidator;

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
        InvoiceEmailer $invoiceEmailer,
        Invalidator $invalidator
    ) {
        parent::__construct(
            $repository,
            $mapper,
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
            $invoiceEmailer
        );
        $this->setInvalidator($invalidator);
    }

    public function save($entity)
    {
        $response = parent::save($entity);
        $this->invalidate($entity);
        return $response;
    }

    public function remove(ItemEntity $entity)
    {
        parent::remove($entity);
        $this->invalidate($entity);
    }

    protected function invalidate(ItemEntity $entity)
    {
        try {
            $this->invalidator->invalidateOrderForOrderItem($entity);
        } catch (Exception $exception) {
            // Ignore invalidation errors
        }
    }

    /**
     * @return self
     */
    protected function setInvalidator(Invalidator $invalidator)
    {
        $this->invalidator = $invalidator;
        return $this;
    }
} 
