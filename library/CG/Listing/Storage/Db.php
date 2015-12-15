<?php
namespace CG\Listing\Storage;

use CG\Listing\Collection;
use CG\Listing\Entity;
use CG\Listing\Filter;
use CG\Listing\StorageInterface;
use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Stdlib\Exception\Storage as StorageException;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Mapper\FromArrayInterface as ArrayMapper;
use CG\Stdlib\CollectionInterface;
use CG\Zend\Stdlib\Db\Sql\InsertIgnore;
use Zend\Db\Sql\Sql as ZendSql;

class Db extends DbAbstract implements StorageInterface
{
    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $select = $this->buildFilterQuery($filter)->columns(['total' => new Expression('COUNT(DISTINCT id)')]);
            $total = $this->getReadSql()->prepareStatementForSqlObject($select)->execute()->current()['total'];

            if ($total == 0) {
                throw new NotFound('No listings match requested filter', 404, null, 'ListingNotFound');
            }

            $select = $this->buildFilterQuery($filter)->quantifier(Select::QUANTIFIER_DISTINCT)->columns(['id']);
            if ($filter->getLimit() != 'all') {
                $offset = ($filter->getPage() - 1) * $filter->getLimit();
                $select->limit($filter->getLimit())
                    ->offset($offset);
            }

            $results = $this->getReadSql()->prepareStatementForSqlObject($select)->execute();
            if ($results->count() == 0) {
                throw new NotFound('No listings match requested filter', 404, null, 'ListingNotFound');
            }

            $listingsId = [];
            foreach ($results as $listingData) {
                $listingsId[] = $listingData['id'];
            }

            $select = $this->getSelect()->where(['id' => $listingsId]);
            $collection = $this->fetchCollection(
                new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray()),
                $this->getReadSql(),
                $select,
                $this->getMapper()
            );
            $collection->setTotal($total);
            return $collection;
        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function fetchCollection(
        CollectionInterface $collection,
        ZendSql $sql,
        Select $select,
        ArrayMapper $arrayMapper,
        $expected = false
    ) {
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        if ($results->count() == 0 || ($expected !== false && $results->count() != $expected)) {
            throw new NotFound("No Listings collection found");
        }

        foreach ($results as $listingData) {
            if ($listing = $collection->getById($listingData['id'])) {
                $listing->appendProductId($listingData['productId']);
                continue;
            }
            $listingData['productIds'] = [$listingData['productId']];
            $collection->attach($arrayMapper->fromArray($listingData));
        }

        return $collection;
    }

    /**
     * @return \Zend\Db\Sql\Select
     */
    protected function buildFilterQuery(Filter $filter)
    {
        $select = $this->getSelect(false);
        if (!empty($filter->getId())) {
            $select->where->in('listing.id', $filter->getId());
        }
        if (!empty($filter->getOrganisationUnitId())) {
            $select->where->in('listing.organisationUnitId', $filter->getOrganisationUnitId());
        }
        if (!empty($filter->getProductId())) {
            $mapSelect = $this->getMapSelect()->columns(['listingId']);
            $mapSelect->where->in('productId', $filter->getProductId());
            $select->where->in('listing.id', $mapSelect);
        }
        if (!empty($filter->getExternalId())) {
            $select->where->in('listing.externalId', $filter->getExternalId());
        }
        if (!empty($filter->getChannel())) {
            $select->where->in('listing.channel', $filter->getChannel());
        }
        if (!empty($filter->getStatus())) {
            $select->where->in('listing.status', $filter->getStatus());
        }
        if (!empty($filter->getAccountId())) {
            $select->where->in('listing.accountId', $filter->getAccountId());
        }
        if (!empty($filter->getStatusExclude())) {
            $select->where->notIn('listing.status', $filter->getStatusExclude());
        }
        if (!empty($filter->getMarketplace())) {
            $select->where->in('listing.marketplace', $filter->getMarketplace());
        }
        return $select;
    }

    public function fetch($id)
    {
        $select = $this->getSelect()->where(array(
            'id' => $id
        ));
        $statement = $this->getReadSql()->prepareStatementForSqlObject($select);

        $results = $statement->execute();
        if ($results->count() == 0) {
            throw new NotFound("Listing $id not found in Db layer", 404, null, "ListingNotFound");
        }

        $listing = $results->current();
        $listing['productIds'] = [];
        if (isset($listing['productId'])) {
            foreach ($results as $listingData) {
                $listing['productIds'][] = $listingData['productId'];
            }
        }
        unset($listing['productId']);
        return $this->getMapper()->fromArray($listing);
    }

    protected function fetchProductIdsForListing($listingId)
    {
        $select = $this->getMapSelect()->where(['listingId' => $listingId]);
        return $this->getReadSql()->prepareStatementForSqlObject($select)->execute();
    }

    protected function insertEntity($entity)
    {
        $listingArr = $entity->toArray();
        $productIds = $listingArr['productIds'];
        unset($listingArr['productIds']);
        $insert = $this->getInsert()->values($listingArr);
        $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        $id = $this->getWriteSql()->getAdapter()->getDriver()->getLastGeneratedValue();
        $this->insertProductIds($id, $productIds);

        $entity->setId($id);
        $entity->setNewlyInserted(true);
    }

    protected function updateEntity($entity)
    {
        $listingArr = $entity->toArray();
        $listingId = $entity->getId();
        $productIds = $listingArr['productIds'];
        $this->deleteProductIds($listingId);
        $this->insertProductIds($listingId, $productIds);
        unset($listingArr['productIds']);
        $update = $this->getUpdate()->set($listingArr)
            ->where(array('id' => $listingId));
        $this->getWriteSql()->prepareStatementForSqlObject($update)->execute();
    }

    public function remove($entity)
    {
        $this->deleteProductIds($entity->getId());
        parent::remove($entity);
    }

    protected function insertProductIds($listingId, array $productIds)
    {
        if (count($productIds) == 0) {
            return;
        }
        $insert = new InsertIgnore('productToListingMap');
        foreach ($productIds as $productId) {
            $insert->values([
                'listingId' => $listingId,
                'productId' => $productId
            ]);
            $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        }
    }

    protected function deleteProductIds($listingId)
    {
        $delete = $this->getMapDelete();
        $delete->where(['listingId' => $listingId]);
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
    }

    protected function getSelect($columns = true)
    {
        return $this->getReadSql()->select('listing')
            ->join(
                'productToListingMap',
                'listing.id=productToListingMap.listingId',
                $columns ? ['productId' => 'productId'] : [],
                Select::JOIN_LEFT
            );
    }

    protected function getInsert()
    {
        return $this->getWriteSql()->insert('listing');
    }

    protected function getUpdate()
    {
        return $this->getWriteSql()->update('listing');
    }

    protected function getDelete()
    {
        return $this->getWriteSql()->delete('listing');
    }

    protected function getMapSelect()
    {
        return $this->getWriteSql()->select('productToListingMap');
    }

    protected function getMapInsert()
    {
        return $this->getWriteSql()->insert('productToListingMap')
            ->columns(['listingId', 'productId']);
    }

    protected function getMapDelete()
    {
        return $this->getWriteSql()->delete('productToListingMap');
    }

    public function getEntityClass()
    {
        return Entity::class;
    }
}
