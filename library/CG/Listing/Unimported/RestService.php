<?php
namespace CG\Listing\Unimported;

use CG\Listing\Unimported\Collection as UnimportedListingCollection;
use CG\Listing\Unimported\Entity as UnimportedListingEntity;
use CG\Listing\Unimported\Filter;
use CG\Listing\Unimported\Service;

class RestService extends Service
{
    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    public function fetchAsHal($id)
    {
        $entity = $this->fetch($id);
        //Converting to Collection removes need for duplicate code throughout the codebase
        $unimportedListingCollection = new UnimportedListingCollection(UnimportedListingEntity::class, __FUNCTION__, compact('id'));
        $unimportedListingCollection->attach($entity);
        $this->fetchCollectionEmbeds($unimportedListingCollection);
        return $this->getMapper()->toHal($entity);
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
            $collection, "/unimportedListing", $filter->getLimit(), $filter->getPage(), $filter->toArray()
        );
    }
}