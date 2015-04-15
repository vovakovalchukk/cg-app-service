<?php
namespace CG\Listing\Service;

use CG\Account\Shared\Entity as AccountEntity;
use CG\Listing\Filter;
use CG\Listing\Mapper;
use CG\Listing\ServiceAbstract;
use CG\Listing\StorageInterface;
use CG\Slim\Patch\ServiceTrait as PatchServiceTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\ServiceTrait;
use Nocarrier\Hal;
use Zend\EventManager\GlobalEventManager;

class Service extends ServiceAbstract implements LoggerAwareInterface
{
    use LogTrait;
    use ServiceTrait;
    use PatchServiceTrait;

    const CHUNK_AMOUNT = 500;

    protected $globalEventManager;

    public function __construct(
        StorageInterface $repository,
        Mapper $mapper,
        GlobalEventManager $globalEventManager
    ) {
        $this->setRepository($repository)
            ->setMapper($mapper)
            ->setGlobalEventManager($globalEventManager);
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

    public function saveHal(Hal $hal, array $ids)
    {
        $listing = $this->fromHal($hal, $ids);
        GlobalEventManager::trigger('listing.update', __CLASS__, ['listing' => $listing]);
        return $this->save($listing);
    }

    public function removeAllListingsForAccount(AccountEntity $accountEntity)
    {
        $filter = (new Filter(static::CHUNK_AMOUNT, 1))->setAccountId([$accountEntity->getId()]);
        $counter = 0;
        try {
            do {
                $listings = $this->getRepository()->fetchCollectionByFilter($filter);
                foreach($listings as $listing) {
                    $this->getRepository()->remove($listing);
                    $counter++;
                }
            } while (true);
        } catch (NotFound $e) {
            $this->logDebug('All listings (%s) have been removed for account %s', [$counter, $accountEntity->getId()]);
            // Do nothing, all the listings will have been removed by this point
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
     * @return StorageInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }
}
