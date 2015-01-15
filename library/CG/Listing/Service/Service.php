<?php
namespace CG\Listing\Service;

use CG\Listing\Filter;
use CG\Listing\Mapper;
use CG\Listing\ServiceAbstract;
use CG\Listing\StorageInterface;
use CG\Slim\Patch\ServiceTrait as PatchServiceTrait;
use CG\Slim\Renderer\ResponseType\Hal;
use CG\Stdlib\ServiceTrait;
use Zend\EventManager\GlobalEventManager;

class Service extends ServiceAbstract
{
    use ServiceTrait;
    use PatchServiceTrait;

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

    /**
     * To satisfy PatchServiceTrait
     */
    protected function getEventManager()
    {
        return $this->getGlobalEventManager();
    }
}
