<?php
namespace CG\Listing\Template;

use CG\Slim\Renderer\ResponseType\Hal;
use CG\Listing\Template\Filter;
use CG\Listing\Template\Service;

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
        return $this->getMapper()->collectionToHal($collection, '/listingTemplate', $filter->getLimit(), $filter->getPage());
    }
}