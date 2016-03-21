<?php
namespace CG\Product\Storage;

use CG\Product\Collection as ProductCollection;
use CG\Product\Entity as ProductEntity;
use CG\Product\Filter;
use CG\Product\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Db\ArrayFiltersToWhereTrait;
use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Stdlib\Storage\Db\FilterArrayValuesToOrdLikesTrait;
use CG\Zend\Stdlib\Db\Sql\InsertIgnore as InsertIgnore;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\In;
use Zend\Db\Sql\Predicate\Like;
use Zend\Db\Sql\Predicate\NotIn;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

/**
 * @method Sql getFastReadSql
 * @method Sql getReadSql
 * @method Sql getWriteSql
 */
class Db extends DbAbstract implements StorageInterface
{
    use ArrayFiltersToWhereTrait;
    use FilterArrayValuesToOrdLikesTrait;

    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $total = $this->fetchEntityCount($filter);
            $ids = $this->fetchEntitiesIds($filter);
            $idInIds = new In('product.id', $ids);
            // Do NOT apply the filter limit to this query as we get multiple rows back per Product
            $select = $this->getSelect();
            $select->where($idInIds);
            $select->order('product.id ASC');
            $productCollection = $this->fetchCollectionWithJoinQuery(
                new ProductCollection($this->getEntityClass(), __FUNCTION__, $filter->toArray()),
                $select
            );
            $productCollection->setTotal($total);
            return $productCollection;
        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function createSelectFromFilter(Filter $filter)
    {
        $select = $this->getReadSql()->select('product')->columns(['id' => 'id']);
        $query = $this->buildFilterQuery($filter);
        $select->where($query);

        if ($filter->getSearchTerm()) {
            $select->join(
                ['variation' => 'product'],
                'variation.parentProductId = product.id',
                [],
                Select::JOIN_LEFT
            );
            $select->where($this->buildSearchTermQuery($filter->getSearchTerm()));
        }

        if ($filter->getReplaceVariationWithParent()) {
            $select->columns(
                ['id' => new Expression('DISTINCT IF(product.parentProductId > 0, product.parentProductId, product.id)')]
            );
        }

        return $select;
    }

    protected function fetchEntityCount(Filter $filter)
    {
        $select = $this->createSelectFromFilter($filter);
        $columns = $select->getRawState(Select::COLUMNS);
        $select->columns(['count' => new Expression('COUNT(?)', [$columns['id']])]);

        $results = $this->getReadSql()->prepareStatementForSqlObject($select)->execute();
        return $results->current()['count'];
    }

    protected function fetchEntitiesIds(Filter $filter)
    {
        $select = $this->createSelectFromFilter($filter);

        if ($filter->getLimit() != 'all') {
            $offset = ($filter->getPage() - 1) * $filter->getLimit();
            $select->limit($filter->getLimit())
                ->offset($offset);
        }

        $results = $this->getReadSql()->prepareStatementForSqlObject($select)->execute();
        if($results->count() == 0) {
            throw new NotFound();
        }
        return array_column(iterator_to_array($results), 'id');
    }

    protected function fetchCollectionWithJoinQuery(ProductCollection $collection, Select $select)
    {
        $rows = $this->getReadSql()->prepareStatementForSqlObject($select)->execute();
        if ($rows->count() == 0) {
            throw new NotFound();
        }
        return $this->getMapper()->fromMysqlRows($rows, $collection);
    }

    protected function buildFilterQuery(Filter $filter)
    {
        $query = [];
        if (!empty($filter->getOrganisationUnitId())) {
            $query['product.organisationUnitId'] = $filter->getOrganisationUnitId();
        }
        if (!empty($filter->getParentProductId())) {
            $query['product.parentProductId'] = $filter->getParentProductId();
        }
        if (!empty($filter->getId())) {
            $query['product.id'] = $filter->getId();
        }
        if (!is_null($filter->isDeleted())) {
            $query['product.deleted'] = $filter->isDeleted();
        }
        if (!is_null($filter->getCgCreationDate())) {
            $query['product.cgCreationDate'] = $filter->getCgCreationDate();
        }
        if (!empty($filter->getStockMode())) {
            $query['product.stockMode'] = $filter->getStockMode();
        }

        if(!empty($filter->getSku())) {
            // Must do SKU check as (LIKE OR LIKE) instead of IN() otherwise
            // MySQL ignores trailing spaces and we get unexpected results
            $sku = (array)$filter->getSku();
            $where = $this->arrayFiltersToWhere($query);
            $this->filterArrayValuesToOrdLikes('product.sku', $sku, $where);
            return $where;
        }
        return $query;
    }

    protected function buildSearchTermQuery($searchTerm)
    {
        $searchQuery = [];
        $searchFields = ['`product`.sku', '`product`.name', '`variation`.sku'];
        $likeSearchTerm  = "%" . \CG\Stdlib\escapeLikeValue($searchTerm) . "%";

        foreach ($searchFields as $field) {
            $searchQuery[] = $field . ' LIKE ?';
        }

        return ["(" . implode(' OR ', $searchQuery) . ")" => array_fill(0, count($searchQuery), $likeSearchTerm)];
    }

    public function fetch($id)
    {
        $select = $this->getSelect()->where(['product.id' => $id]);
        $products = $this->fetchCollectionWithJoinQuery(new ProductCollection($this->getEntityClass(), __FUNCTION__), $select);
        return $products->getById($id);
    }

    /**
     * @param ProductEntity $entity
     */
    protected function insertEntity($entity)
    {
        $entityArray = $entity->toArray();
        unset($entityArray['attributeNames'], $entityArray['attributeValues'], $entityArray['imageIds']);

        $insert = $this->getInsert()->values($entityArray);
        $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        $id = $this->getWriteSql()->getAdapter()->getDriver()->getLastGeneratedValue();

        $entity->setId($id);
        $entity->setNewlyInserted(true);

        $this->saveAttributeRelation($entity);
        $this->saveImageRelation($entity);
        return $entity;
    }

    protected function saveAttributeRelation(ProductEntity $entity)
    {
        $productAttributeInsert = new InsertIgnore('productAttribute');
        foreach($entity->getAttributeNames() as $attributeName) {
            $productAttributeInsert->values([
                'productId' => $entity->getId(),
                'name' => $attributeName
            ]);
            $this->getWriteSql()->prepareStatementForSqlObject($productAttributeInsert)->execute();
        }
        $productAttributeValueInsert = $this->getWriteSql()->insert('productAttributeValue');
        foreach($entity->getAttributeValues() as $attributeName => $attributeValue) {
            $select = $this->getWriteSql()->select('productAttribute')->where([
                new Like('productAttribute.name', \CG\Stdlib\escapeLikeValue($attributeName)),
                'productAttribute.productId' => $entity->getParentProductId()
            ]);
            $rows = $this->getWriteSql()->prepareStatementForSqlObject($select)->execute();
            $rows->rewind();
            $row = $rows->current();
            $productAttributeId = $row['id'];
            $productAttributeValueInsert->values([
                'productId' => $entity->getId(),
                'value' => $attributeValue,
                'productAttributeId' => $productAttributeId
            ]);
            $this->getWriteSql()->prepareStatementForSqlObject($productAttributeValueInsert)->execute();
        }
    }

    /**
     * @param ProductEntity $entity
     */
    protected function updateEntity($entity)
    {
        $entityArray = $entity->toArray();
        unset($entityArray['attributeNames'], $entityArray['attributeValues'], $entityArray['imageIds']);

        $update = $this->getUpdate()->set($entityArray)
            ->where(array('id' => $entity->getId()));
        $this->getWriteSql()->prepareStatementForSqlObject($update)->execute();

        $this->removeAttributeRelation($entity);
        $this->saveAttributeRelation($entity);
        $this->saveImageRelation($entity);

        return $entity;
    }

    protected function removeAttributeRelation(ProductEntity $entity)
    {
        $delete = $this->getWriteSql()->delete('productAttribute');
        $query = [
            'productId' => $entity->getId(),
        ];
        if (!empty($entity->getAttributeNames())) {
            $query[] = new NotIn('name', $entity->getAttributeNames());
        }
        $delete->where($query);
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
        $delete = $this->getWriteSql()->delete('productAttributeValue');
        $delete->where([
            'productId' => $entity->getId()
        ]);
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
    }

    protected function saveImageRelation(ProductEntity $entity)
    {
        $delete = $this->getWriteSql()->delete('productImage');
        $query = [
            'productId' => $entity->getId(),
        ];
        $delete->where($query);
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
        $productAttributeValueInsert = $this->getWriteSql()->insert('productImage');
        foreach ($entity->getImageIds() as $order => $imageId) {
            $productAttributeValueInsert->values([
                'order' => $order,
                'imageId' => $imageId,
                'productId' => $entity->getId()
            ]);
            $this->getWriteSql()->prepareStatementForSqlObject($productAttributeValueInsert)->execute();
        }
    }

    /**
     * @return Select
     */
    protected function getSelect()
    {
        return $this->getReadSql()->select('product')
            ->join(
                'productImage',
                'productImage.productId = product.id',
                ['imageId', 'order'],
                Select::JOIN_LEFT
            )
            ->join(
                'productAttribute',
                'productAttribute.productId = product.id',
                ['attributeName' => 'name'],
                Select::JOIN_LEFT
            )->join(
                ['parent' => 'product'],
                'parent.id = product.parentProductId',
                [],
                Select::JOIN_LEFT
            )->join(
                ['parentProductAttribute' => 'productAttribute'],
                'parentProductAttribute.productId = parent.id',
                ['parentAttributeName' => 'name'],
                Select::JOIN_LEFT
            )->join(
                'productAttributeValue',
                'productAttributeValue.productId = product.id AND productAttributeValue.productAttributeId = parentProductAttribute.id',
                ['attributeValue' => 'value'],
                Select::JOIN_LEFT
            );
    }

    protected function getInsert()
    {
        return $this->getWriteSql()->insert('product');
    }

    protected function getUpdate()
    {
        return $this->getWriteSql()->update('product');
    }

    protected function getDelete()
    {
        return $this->getWriteSql()->delete('product');
    }

    public function getEntityClass()
    {
        return ProductEntity::class;
    }
}
