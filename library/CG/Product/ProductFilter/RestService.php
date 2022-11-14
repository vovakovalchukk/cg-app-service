<?php
namespace CG\Product\ProductFilter;

use CG\Product\ProductFilter\Entity as ProductFilter;
use CG\Stdlib\Exception\Runtime\NotFound;

class RestService extends Service
{
    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    /**
     * @param ProductFilter $entity
     * @return mixed
     */
    public function save($entity, array $adjustmentIds = [])
    {
        try {
            if (is_null($entity->getId())) {
                throw new NotFound();
            }
            $previousEntity = $this->fetch($entity->getId());
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
            $collection, "/productFilter", $filter->getLimit(), $filter->getPage(), $filter->toArray()
        );
    }
}
