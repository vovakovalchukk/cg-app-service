<?php
namespace CG\Settings\Order;

use CG\Settings\Order\Filter;
use CG\Settings\Order\Mapper;
use CG\Settings\Order\Service;
use Nocarrier\Hal;

class RestService extends Service
{
    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    /**
     * @return \CG\Slim\Renderer\ResponseType\Hal
     */
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
            $collection, Mapper::URL, $filter->getLimit(), $filter->getPage(), $filter->toArray()
        );
    }

    public function saveHal(Hal $hal, array $ids)
    {
        $entity = $this->fromHal($hal, $ids);
        return $this->getMapper()->toHal($this->save($entity));
    }
}
