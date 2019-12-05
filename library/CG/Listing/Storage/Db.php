<?php
namespace CG\Listing\Storage;

use CG\Listing\Collection;
use CG\Listing\Entity;
use CG\Listing\Filter;
use CG\Listing\StorageInterface;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Where;

/**
 * @method Sql getReadSql
 * @method Sql getFastReadSql
 * @method Sql getWriteSql
 */
class Db extends DbAbstract implements StorageInterface
{
    const TABLE = 'listing';
    const TABLE_PRODUCT_MAP = 'productToListingMap';
    const TABLE_LISTING_EXTERNAL_ID_MAP = 'listingSkuToExternalIdMap';

    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $select = $this->buildFilterQuery($filter);
            $this->applyLimitToSelect($select, $filter->getLimit(), $filter->getPage());

            /** @var Collection $collection */
            $collection = $this->fetchPaginatedCollection(
                new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray()),
                $this->getReadSql(),
                $select,
                $this->getMapper()
            );

            foreach ($this->fetchAssociatedProductInformation($collection->getIds()) as $productInformation) {
                /** @var Entity $listing */
                $listing = $collection->getById($productInformation['listingId']);
                $listing->appendProductId($productInformation['productId']);
                $listing->appendProductSku($productInformation['productId'], $productInformation['productSku']);
            }
            foreach ($this->fetchAssociatedListingExternalIds($collection->getIds()) as $externalIds) {
                /** @var Entity $listing */
                $listing = $collection->getById($externalIds['listingId']);
                $listing->appendExternalId($externalIds['listingSku'], $externalIds['externalId']);
            }

            return $collection;
        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function buildFilterQuery(Filter $filter)
    {
        $select = $this->getSelect()->group(static::TABLE . '.id');

        if (!empty($filter->getId())) {
            $select->where->in(static::TABLE . '.id', $filter->getId());
        }
        if (!empty($filter->getOrganisationUnitId())) {
            $select->where->in(static::TABLE . '.organisationUnitId', $filter->getOrganisationUnitId());
        }
        if (!empty($filter->getProductId())) {
            $select->join(
                static::TABLE_PRODUCT_MAP,
                static::TABLE . '.id=' . static::TABLE_PRODUCT_MAP . '.listingId',
                [],
                Select::JOIN_INNER
            );
            $select->where->in(static::TABLE_PRODUCT_MAP . '.productId', $filter->getProductId());
        }
        if (!empty($filter->getChannel())) {
            $select->where->in(static::TABLE . '.channel', $filter->getChannel());
        }
        if (!empty($filter->getStatus())) {
            $select->where->in(static::TABLE . '.status', $filter->getStatus());
        }
        if (!empty($filter->getAccountId())) {
            $select->where->in(static::TABLE . '.accountId', $filter->getAccountId());
        }
        if (!empty($filter->getStatusExclude())) {
            $select->where->notIn(static::TABLE . '.status', $filter->getStatusExclude());
        }
        if (!empty($filter->getMarketplace())) {
            $select->where->in(static::TABLE . '.marketplace', $filter->getMarketplace());
        }
        if (!empty($filter->getUrl())) {
            $select->where->in(static::TABLE . '.url', $filter->getUrl());
        }

        return $this->restrictSelectByExternalId($select, $filter->getExternalId());
    }

    protected function restrictSelectByExternalId(Select $select, array $externalIds)
    {
        if (empty($externalIds)) {
            return $select;
        }

        $listingExternalIdMap = clone $select;
        $listingExternalIdMap->join(
            static::TABLE_LISTING_EXTERNAL_ID_MAP,
            static::TABLE . '.id=' . static::TABLE_LISTING_EXTERNAL_ID_MAP . '.listingId',
            [],
            Select::JOIN_INNER
        );
        $listingExternalIdMap->where->in(static::TABLE_LISTING_EXTERNAL_ID_MAP . '.externalId', $externalIds);

        $select->where->in(static::TABLE . '.externalId', $externalIds);
        $select->combine($listingExternalIdMap, Select::COMBINE_UNION, Select::QUANTIFIER_DISTINCT);

        return $this->getSelect(['listings' => $select]);
    }

    protected function applyLimitToSelect(Select $select, $limit, $page)
    {
        if ($limit != 'all') {
            $select->limit($limit)->offset(($page - 1) * $limit);
        }
    }

    public function fetch($id)
    {
        /** @var Entity $listing */
        $listing = $this->fetchEntity($this->getReadSql(), $this->getSelect()->where(['id' => $id]), $this->getMapper());
        foreach ($this->fetchAssociatedProductInformation([$listing->getId()]) as $productInformation) {
            $listing->appendProductId($productInformation['productId']);
            $listing->appendProductSku($productInformation['productId'], $productInformation['productSku']);
        }
        foreach ($this->fetchAssociatedListingExternalIds([$listing->getId()]) as $externalIds) {
            $listing->appendExternalId($externalIds['listingSku'], $externalIds['externalId']);
        }
        return $listing;
    }

    protected function fetchAssociatedProductInformation(array $listingIds)
    {
        $select = $this->getSelect(static::TABLE_PRODUCT_MAP)->where(['listingId' => $listingIds]);
        return $this->getReadSql()->prepareStatementForSqlObject($select)->execute();
    }

    protected function fetchAssociatedListingExternalIds(array $listingIds)
    {
        $select = $this->getSelect(static::TABLE_LISTING_EXTERNAL_ID_MAP)->where(['listingId' => $listingIds]);
        return $this->getReadSql()->prepareStatementForSqlObject($select)->execute();
    }
    /**
     * @param Entity $entity
     */
    protected function insertEntity($entity)
    {
        parent::insertEntity($entity);
        $this->insertAssociatedProductInformation($entity, $entity->getProductIds(), $entity->getProductSkus());
        $this->insertAssociatedListingExternalIds($entity, $entity->getSkuExternalIdMap());
    }

    /**
     * @param Entity $entity
     */
    protected function updateEntity($entity)
    {
        parent::updateEntity($entity);
        $this->deleteAssociatedProductInformation($entity);
        $this->insertAssociatedProductInformation($entity, $entity->getProductIds(), $entity->getProductSkus());
        $this->deleteAssociatedListingExternalIds($entity);
        $this->insertAssociatedListingExternalIds($entity, $entity->getSkuExternalIdMap());
    }

    /**
     * @param Entity $entity
     */
    protected function getEntityArray($entity)
    {
        $array = $entity->toArray();
        unset($array['productIds'], $array['productSkus'], $array['skuExternalIdMap']);
        return $array;
    }

    public function remove($entity)
    {
        $this->deleteAssociatedProductInformation($entity);
        $this->deleteAssociatedListingExternalIds($entity);
        parent::remove($entity);
    }

    protected function insertAssociatedProductInformation(Entity $listing, array $productIds, array $productSkus)
    {
        $productSkus = $productSkus + array_fill_keys($productIds, '');
        if (empty($productIds) && empty($productSkus)) {
            return;
        }

        $insert = $this->getInsert(static::TABLE_PRODUCT_MAP);
        foreach ($productSkus as $productId => $productSku) {
            $insert->values(
                [
                    'listingId' => $listing->getId(),
                    'productId' => $productId,
                    'productSku' => $productSku,
                ]
            );
            $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        }

        $listing->setProductIds($productIds)->setProductSkus($productSkus);
    }

    protected function deleteAssociatedProductInformation(Entity $listing)
    {
        $delete = $this->getDelete(static::TABLE_PRODUCT_MAP)->where(['listingId' => $listing->getId()]);
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
    }

    protected function insertAssociatedListingExternalIds(Entity $listing, array $skuExternalIdMap)
    {
        $insert = $this->getInsert(static::TABLE_LISTING_EXTERNAL_ID_MAP);
        foreach ($skuExternalIdMap as $sku => $externalId) {
            $insert->values(
                [
                    'listingId' => $listing->getId(),
                    'listingSku' => $sku,
                    'externalId' => $externalId,
                ]
            );
            $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        }
        $listing->setSkuExternalIdMap($skuExternalIdMap);
    }

    protected function deleteAssociatedListingExternalIds(Entity $listing)
    {
        $delete = $this->getDelete(static::TABLE_LISTING_EXTERNAL_ID_MAP)->where(['listingId' => $listing->getId()]);
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
    }

    /**
     * @return Select
     */
    protected function getSelect($table = self::TABLE)
    {
        return $this->getReadSql()->select($table);
    }

    /**
     * @return Insert
     */
    protected function getInsert($table = self::TABLE)
    {
        return $this->getWriteSql()->insert($table);
    }

    /**
     * @return Update
     */
    protected function getUpdate($table = self::TABLE)
    {
        return $this->getWriteSql()->update($table);
    }

    /**
     * @return Delete
     */
    protected function getDelete($table = self::TABLE)
    {
        return $this->getWriteSql()->delete($table);
    }

    public function getEntityClass()
    {
        return Entity::class;
    }
}
