<?php
namespace CG\Product\Detail\Storage;

use CG\Product\Detail\Collection;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Product\Detail\Filter as Filter;
use CG\Product\Detail\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Expression;

class Db extends DbAbstract implements StorageInterface
{
    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $query = $this->buildFilterQuery($filter);
            $total = $this->fetchEntityCount($query);
            if ($total == 0) {
                throw new NotFound('No matching ProductDetails found matching requested filters');
            }
            $select = $this->getSelect()->quantifier(Select::QUANTIFIER_DISTINCT)->columns(['id'])->where($query);

            if ($filter->getLimit() != 'all') {
                $offset = ($filter->getPage() - 1) * $filter->getLimit();
                $select->limit($filter->getLimit())
                    ->offset($offset);
            }

            $ids = array_column(
                iterator_to_array($this->getReadSql()->prepareStatementForSqlObject($select)->execute()),
                'id'
            );

            if (empty($ids)) {
                throw new NotFound('No matching ProductDetails found matching requested filters');
            }

            $productDetails = $this->fetchCollection(
                new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray()),
                $this->getReadSql(),
                $this->getSelect()->where(['id' => $ids]),
                $this->getMapper()
            );
            $productDetails->setTotal($total);
            return $productDetails;

        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function fetchEntityCount($query)
    {
        $select = $this->getReadSql()
            ->select('productDetail')
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(['count' => new Expression('COUNT(id)')])
            ->where($query);

        $results = $this->getReadSql()->prepareStatementForSqlObject($select)->execute();
        return $results->current()['count'];
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
            $query['productDetail.ean'] = $filter->getEan();
        }
        if (!empty($filter->getBrand())) {
            $query['productDetail.brand'] = $filter->getBrand();
        }
        if (!empty($filter->getMpn())) {
            $query['productDetail.mpn'] = $filter->getMpn();
        }
        if (!empty($filter->getAsin())) {
            $query['productDetail.asin'] = $filter->getAsin();
        }
        if (!empty($filter->getCategoryTemplateId())) {
            $query['productCategoryTemplate.categoryTemplateId'] = $filter->getCategoryTemplateId();
        }
        if (!empty($filter->getUpc())) {
            $query['productDetail.upc'] = $filter->getUpc();
        }
        if (!empty($filter->getIsbn())) {
            $query['productDetail.isbn'] = $filter->getIsbn();
        }
        if (!empty($filter->getGtin())) {
            $query['productDetail.gtin'] = $filter->getGtin();
        }
        if (!empty($filter->getSupplierId())) {
            $query['productDetail.supplierId'] = $filter->getSupplierId();
        }
        return $query;
    }

    protected function saveEntity($entity)
    {
        /** @var ProductDetail $entity */
        $entity = parent::saveEntity($entity);
        $this->saveCategoryTemplateIds($entity->getId(), $entity->getCategoryTemplateIds());
        return $entity;
    }

    protected function saveCategoryTemplateIds(int $id, array $categoryTemplateIds)
    {
        $delete = $this->getDelete('productCategoryTemplate')->where(['productId' => $id]);
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();

        foreach ($categoryTemplateIds as $categoryTemplateId) {
            $insert = $this->getInsert('productCategoryTemplate')->values([
                'productId' => $id,
                'categoryTemplateId' => $categoryTemplateId,
            ]);
            $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        }
    }

    protected function getEntityArray($entity)
    {
        $array = parent::getEntityArray($entity);
        unset($array['categoryTemplateIds']);
        return $array;
    }

    protected function getSelect(): Select
    {
        /** @var Select $select */
        $select = $this->getReadSql()->select('productDetail');
        $select->join(
            'productCategoryTemplate',
            'productDetail.id = productCategoryTemplate.productId',
            ['categoryTemplateIds' => new Expression('GROUP_CONCAT(? SEPARATOR ",")', ['productCategoryTemplate.categoryTemplateId'], [Expression::TYPE_IDENTIFIER])],
            Select::JOIN_LEFT
        );
        $select->group('productDetail.id');
        return $select;
    }

    protected function getInsert($table = 'productDetail'): Insert
    {
        return $this->getWriteSql()->insert($table);
    }

    protected function getUpdate($table = 'productDetail'): Update
    {
        return $this->getWriteSql()->update($table);
    }

    protected function getDelete($table = 'productDetail'): Delete
    {
        return $this->getWriteSql()->delete($table);
    }

    public function getEntityClass()
    {
        return ProductDetail::class;
    }
}