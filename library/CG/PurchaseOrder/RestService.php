<?php
namespace CG\PurchaseOrder;

use Zend\EventManager\GlobalEventManager as EventManager;

class RestService extends Service
{
    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    /** @var EventManager $eventManager */
    protected $eventManager;

    public function __construct(EventManager $eventManager, StorageInterface $repository, Mapper $mapper)
    {
        parent::__construct($repository, $mapper);
        $this->eventManager = $eventManager;
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
            $collection, "/purchaseOrder", $filter->getLimit(), $filter->getPage(), $filter->toArray()
        );
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
