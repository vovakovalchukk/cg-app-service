<?php
namespace CG\Listing\Service;

use CG\Account\Client\Entity as AccountEntity;
use CG\Channel\ChannelActions;
use CG\Listing\Collection;
use CG\Listing\Entity;
use CG\Listing\Filter;
use CG\Listing\Mapper;
use CG\Listing\ServiceAbstract;
use CG\Listing\StatusChecker;
use CG\Listing\StatusHistory\Filter as StatusHistoryFilter;
use CG\Listing\StatusHistory\Service as StatusHistoryService;
use CG\Listing\StorageInterface;
use CG\Slim\Patch\ServiceTrait as PatchServiceTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\ServiceTrait;
use Nocarrier\Hal;
use Zend\EventManager\GlobalEventManager;

class Service extends ServiceAbstract
{
    use ServiceTrait {
        fetch as fetchTrait;
    }
    use PatchServiceTrait;

    const CHUNK_AMOUNT = 500;

    protected $globalEventManager;
    /** @var StatusHistoryService $statusHistoryService */
    protected $statusHistoryService;
    /** @var ChannelActions $channelActions */
    protected $channelActions;

    public function __construct(
        StorageInterface $repository,
        Mapper $mapper,
        GlobalEventManager $globalEventManager,
        StatusChecker $statusChecker,
        StatusHistoryService $statusHistoryService,
        ChannelActions $channelActions
    ) {
        parent::__construct($repository, $mapper, $statusChecker);
        $this
            ->setGlobalEventManager($globalEventManager)
            ->setStatusHistoryService($statusHistoryService)
            ->setChannelActions($channelActions);
    }

    public function fetch($id)
    {
        /** @var Entity $entity */
        $entity = $this->fetchTrait($id);
        try {
            $filter = (new StatusHistoryFilter('all'))->setListingId([$entity->getId()])->setLatest(true);
            $statusHistory = $this->statusHistoryService->fetchCollectionByFilter($filter);
            $entity->setStatusHistory($statusHistory);
        } catch (NotFound $exception) {
            // No status history found for listing
        }
        return $entity;
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
            $collection, "/listing", $filter->getLimit(), $filter->getPage(), $filter->toArray()
        );
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        /** @var Collection $collection */
        $collection = parent::fetchCollectionByFilter($filter);

        try {
            $filter = (new StatusHistoryFilter('all'))->setListingId($collection->getIds())->setLatest(true);
            $statusHistory = $this->statusHistoryService->fetchCollectionByFilter($filter);
        } catch (NotFound $exception) {
            // No status history found for listings
        }

        /** @var Entity $entity */
        foreach ($collection as $entity) {
            if (isset($statusHistory)) {
                $entity->setStatusHistory($statusHistory->getByListingId($entity->getId()));
            }
        }

        return $collection;
    }

    public function saveHal(Hal $hal, array $ids)
    {
        $listing = $this->fromHal($hal, $ids);
        GlobalEventManager::trigger('listing.update', __CLASS__, ['listing' => $listing]);
        return $this->save($listing);
    }

    public function remove($entity)
    {
        parent::remove($entity);
        $this->channelActions->listingDeleted($entity);
    }

    public function removeAllListingsForAccount(AccountEntity $accountEntity)
    {
        $filter = (new Filter(static::CHUNK_AMOUNT, 1))->setAccountId([$accountEntity->getId()]);
        $counter = 0;
        try {
            do {
                $listings = $this->getRepository()->fetchCollectionByFilter($filter);
                foreach($listings as $listing) {
                    $this->remove($listing);
                    $counter++;
                }
            } while (true);
        } catch (NotFound $e) {
            // Return the amount removed, all the listings should have been removed by this point
            return $counter;
        }
    }

    protected function getGlobalEventManager()
    {
        return $this->globalEventManager;
    }

    protected function setGlobalEventManager(GlobalEventManager $globalEventManager)
    {
        $this->globalEventManager = $globalEventManager;
        return $this;
    }

    /**
     * To satisfy PatchServiceTrait
     */
    protected function getEventManager()
    {
        return $this->getGlobalEventManager();
    }

    /**
     * @return self
     */
    protected function setStatusHistoryService(StatusHistoryService $statusHistoryService)
    {
        $this->statusHistoryService = $statusHistoryService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setChannelActions(ChannelActions $channelActions)
    {
        $this->channelActions = $channelActions;
        return $this;
    }
}
