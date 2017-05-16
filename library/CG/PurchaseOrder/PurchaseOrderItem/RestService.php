<?php
namespace CG\PurchaseOrder\PurchaseOrderItem;
use CG\Slim\Patch\ServiceTrait as PatchTrait;
use Zend\EventManager\GlobalEventManager as EventManager;

class RestService extends Service
{
    use PatchTrait;

    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    /** @var EventManager $eventManager */
    protected $eventManager;

    public function __construct(EventManager $eventManager, StorageInterface $repository, Mapper $mapper)
    {
        parent::__construct($repository, $mapper);
        $this->setEventManager($eventManager);
    }

    public function fetchCollectionByFilterAsHal(Filter $filter)
    {

        if (!$filter->getLimit()) {
            $filter->setLimit(static::DEFAULT_LIMIT);
        }
        if (!$filter->getPage()) {
            $filter->setPage(static::DEFAULT_PAGE);
        }

        $collection = $this->fetchCollectionByFilter($filter);
        return $this->getMapper()->collectionToHal(
            $collection, "/purchaseOrderItem", $filter->getLimit(), $filter->getPage(), $filter->toArray()
        );
    }

    /**
     * @return self
     */
    protected function setEventManager(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
        return $this;
    }

    /**
     * Required by PatchTrait
     * @return EventManager
     */
    protected function getEventManager()
    {
        return $this->eventManager;
    }
}
