<?php
namespace CG\Product\Detail\Storage;

use CG\Product\Detail\Collection;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Product\Detail\Filter as Filter;
use CG\Product\Detail\StorageInterface;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Exception\ExceptionInterface;

class Db extends DbAbstract implements StorageInterface
{
    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $query = $this->buildFilterQuery($filter);
            $select = $this->getSelect()->where($query);

            if ($filter->getLimit() != 'all') {
                $offset = ($filter->getPage() - 1) * $filter->getLimit();
                $select->limit($filter->getLimit())
                    ->offset($offset);
            }

            return $this->fetchPaginatedCollection(
                new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray()),
                $this->getReadSql(),
                $select,
                $this->getMapper()
            );

        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function buildFilterQuery(Filter $filter)
    {
        $query = [];
        if (!empty($filter->getId())) {
            $query['productDetail.id'] = $filter->getId();
        }
        if (!empty($filter->getOrganisationUnitId())) {
            $query['productDetail.organisationUnitId'] = $filter->getOrganisationUnitId();
        }
        if (!empty($filter->getSku())) {
            $query['productDetail.sku'] = $filter->getSku();
        }
        if (!empty($filter->getEan())) {
            $query['productDetail.ean'] = $filter->getSku();
        }
        if (!empty($filter->getBrand())) {
            $query['productDetail.brand'] = $filter->getSku();
        }
        if (!empty($filter->getMpn())) {
            $query['productDetail.mpn'] = $filter->getSku();
        }
        if (!empty($filter->getAsin())) {
            $query['productDetail.asin'] = $filter->getSku();
        }
        return $query;
    }

    protected function getSelect()
    {
        return $this->getReadSql()->select('productDetail');
    }

    protected function getInsert()
    {
        return $this->getWriteSql()->insert('productDetail');
    }

    protected function getUpdate()
    {
        return $this->getWriteSql()->update('productDetail');
    }

    protected function getDelete()
    {
        return $this->getWriteSql()->delete('productDetail');
    }

    public function getEntityClass()
    {
        return ProductDetail::class;
    }
}