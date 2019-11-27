<?php
namespace CG\Supplier;

use CG\Slim\Renderer\ResponseType\Hal;

class RestService extends Service
{
    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    public function fetchCollectionByFilterAsHal(Filter $filter): Hal
    {
        if (!$filter->getPage()) {
            $filter->setPage(static::DEFAULT_PAGE);
        }
        if (!$filter->getLimit()) {
            $filter->setLimit(static::DEFAULT_LIMIT);
        }

        $collection = $this->getRepository()->fetchCollectionByFilter($filter);
        return $this->getMapper()->collectionToHal($collection, '/supplier', $filter->getLimit(), $filter->getPage());
    }
}