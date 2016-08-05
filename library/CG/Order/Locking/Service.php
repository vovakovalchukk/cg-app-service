<?php
namespace CG\Order\Locking;

use CG\Account\Client\Service as AccountService;
use CG\Locking\Service as LockingService;
use CG\Notification\Gearman\Generator\Dispatcher as Notifier;
use CG\Order\Service\Alert\Service as AlertService;
use CG\Order\Service\Batch\Service as BatchService;
use CG\Order\Service\Filter\Entity\StorageInterface as FilterEntityStorage;
use CG\Order\Service\Filter\StorageInterface as FilterStorage;
use CG\Order\Service\InvoiceEmailer\Service as InvoiceEmailer;
use CG\Order\Service\InvoiceNumber\Assigner as InvoiceNumberAssigner;
use CG\Order\Service\Item\Service as ItemService;
use CG\Order\Service\Note\Service as NoteService;
use CG\Order\Service\Service as OrderService;
use CG\Order\Service\Tracking\Service as TrackingService;
use CG\Order\Service\UserChange\Service as UserChangeService;
use CG\Order\Shared\CustomerCounts\Service as CustomerCountService;
use CG\Order\Shared\FetchCollectionByFilterInterface;
use CG\Order\Shared\Mapper as OrderMapper;
use CG\Order\Shared\OrderCounts\Mapper as OrderCountsMapper;
use CG\Order\Shared\OrderCounts\Storage\Redis as OrderCountsRedis;
use CG\Order\Shared\Status as OrderStatus;
use CG\Order\Shared\StorageInterface as OrderStorage;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use Zend\EventManager\GlobalEventManager;

class Service extends OrderService
{
    /** @var LockingService $lockingService */
    protected $lockingService;

    public function __construct(
        OrderStorage $repository,
        FetchCollectionByFilterInterface $storage,
        OrderMapper $mapper,
        ItemService $itemService,
        NoteService $noteService,
        TrackingService $trackingService,
        AlertService $alertService,
        UserChangeService $userChangeService,
        FilterStorage $filterStorage,
        OrderStatus $orderStatus,
        OrganisationUnitService $organisationUnitService,
        GlobalEventManager $eventManager,
        InvoiceNumberAssigner $invoiceNumberAssigner,
        InvoiceEmailer $invoiceEmailer,
        Notifier $notifier,
        OrderCountsMapper $orderCountsMapper,
        BatchService $batchService,
        OrderCountsRedis $orderCountsRedis,
        FilterEntityStorage $filterEntityStorage,
        AccountService $accountService,
        CustomerCountService $customerCountService,
        LockingService $lockingService
    ) {
        parent::__construct(
            $repository,
            $storage,
            $mapper->setMapToEntityClass(Entity::class),
            $itemService,
            $noteService,
            $trackingService,
            $alertService,
            $userChangeService,
            $filterStorage,
            $orderStatus,
            $organisationUnitService,
            $eventManager,
            $invoiceNumberAssigner,
            $invoiceEmailer,
            $notifier,
            $orderCountsMapper,
            $batchService,
            $orderCountsRedis,
            $filterEntityStorage,
            $accountService,
            $customerCountService
        );
        $this->setLockingService($lockingService);
    }

    /**
     * @inheritDoc
     * @param Entity $entity
     */
    public function save($entity)
    {
        try {
            $lock = $this->lockingService->lock($entity);
            return parent::save($entity);
        } finally {
            $this->lockingService->unlock($lock);
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
