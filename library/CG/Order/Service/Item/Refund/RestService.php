<?php
namespace CG\Order\Service\Item\Refund;

use CG\Order\Shared\Item\Refund\Filter;
use CG\Order\Shared\Item\Refund\Service;
use CG\Slim\Renderer\ResponseType\Hal;

class RestService extends Service
{
    protected const DEFAULT_LIMIT = 10;
    protected const DEFAULT_PAGE = 1;

    public function fetchCollectionByFilterAsHal(Filter $filter): Hal
    {
        if (!$filter->getPage()) {
            $filter->setPage(static::DEFAULT_PAGE);
        }
        if (!$filter->getLimit()) {
            $filter->setLimit(static::DEFAULT_LIMIT);
        }
        $collection = $this->getRepository()->fetchCollectionByFilter($filter);
        return $this->getMapper()->collectionToHal($collection, '/orderItemRefund', $filter->getLimit(), $filter->getPage());
    }
}