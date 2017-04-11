<?php
namespace CG\Order\Service\OrderLink;

use CG\Order\Shared\OrderLink\Filter;
use CG\Order\Shared\OrderLink\Service;

class RestService extends Service
{
    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

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
            $collection, "/orderLink", $filter->getLimit(), $filter->getPage(), $filter->toArray()
        );
    }
}