<?php
namespace CG\Product\ProductSort;

use CG\Product\ProductSort\Entity as ProductSort;
use CG\Stdlib\Exception\Runtime\NotFound;

class RestService extends Service
{
    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    /**
     * @param ProductSort $entity
     * @return mixed
     */
    public function save($entity, array $adjustmentIds = [])
    {
        try {
            if (is_null($entity->getId())) {
                throw new NotFound();
            }
        } catch (NotFound $exception) {
            //noop
        }
        return parent::save($entity);
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
            $collection, "/productSort", $filter->getLimit(), $filter->getPage(), $filter->toArray()
        );
    }
}
