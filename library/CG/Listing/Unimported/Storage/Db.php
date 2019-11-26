<?php
namespace CG\Listing\Unimported\Storage;

use CG\Listing\Unimported\Collection;
use CG\Listing\Unimported\Entity;
use CG\Listing\Unimported\Filter;
use CG\Listing\Unimported\StorageInterface;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Stdlib\Storage\Db\ArrayFiltersToWhereTrait;
use CG\Stdlib\Storage\Db\FilterArrayValuesToOrdLikesTrait;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Predicate\Between;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Update;

class Db extends DbAbstract implements StorageInterface
{
    use ArrayFiltersToWhereTrait;
    use FilterArrayValuesToOrdLikesTrait;

    const VARIATION_TABLE = 'unimportedListingVariation';

    protected $searchFields = [
        'unimportedListing' => [
            'sku',
            'title'
        ],
        // This is used as the alias of unimportedListingVariations version 1
        'ulvs1' => [
            'sku'
        ]
    ];

    public function fetch($id)
    {
        return $this->fetchEntity(
            $this->getReadSql(),
            $this->getSelect()->where(array(
                'unimportedListing.id' => $id
            )),
            $this->getMapper()
        );
    }

    protected function getEntityArray($entity)
    {
        $entityArray =  $entity->toArray();
        unset($entityArray['variationSkus']);
        return $entityArray;
    }

    public function save($entity)
    {
        parent::save($entity);
        if ($entity->isNewlyInserted()) {
            $this->saveVariationSkus($entity->getId(), $entity->getVariationSkus());
            return $entity;
        }
        $this->updateVariationSkus($entity);
        return $entity;
    }

    public function remove($entity)
    {
        $this->deleteVariationSkus($entity->getId());
        parent::remove($entity);
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $select = $this->getSelect();
            $query = $this->buildFilterQuery($select, $filter);
            $select->where($query);
            $this->addSearchTermQuery($select, $filter);

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

    protected function isSelectJoinedTo(Select $select, $tableOrAlias)
    {
        $joined = array_filter(
            $select->getRawState($select::JOINS),
            function($join) use($tableOrAlias) {
                if (!is_array($join)) {
                    return $join['name'] == $tableOrAlias;
                }
                return key($join['name']) == $tableOrAlias || current($join['name']) == $tableOrAlias;
            }
        );

        return !empty($joined);
    }

    protected function joinToUnimportedListingVariationTable(Select $select)
    {
        if ($this->isSelectJoinedTo($select, 'ulvs1')) {
            return;
        }

        $select->join(
            ['ulvs1' => 'unimportedListingVariation'],
            'ulvs1.unimportedListingId = unimportedListing.id',
            [],
            $select::JOIN_LEFT
        );
    }

    protected function buildFilterQuery(Select $select, Filter $filter)
    {
        $query = [];
        if (!empty($filter->getId())) {
            $query['unimportedListing.id'] = $filter->getId();
        }
        if (!empty($filter->getOrganisationUnitId())) {
            $query['unimportedListing.organisationUnitId'] = $filter->getOrganisationUnitId();
        }
        if (!empty($filter->getAccountId())) {
            $query['unimportedListing.accountId'] = $filter->getAccountId();
        }
        if (!empty($filter->getExternalId())) {
            $query['unimportedListing.externalId'] = $filter->getExternalId();
        }
        if (!empty($filter->getTitle())) {
            $query['unimportedListing.title'] = $filter->getTitle();
        }
        if (!empty($filter->getUrl())) {
            $query['unimportedListing.url'] = $filter->getUrl();
        }
        if (!empty($filter->getImageId())) {
            $query['unimportedListing.imageId'] = $filter->getImageId();
        }
        if ($filter->getCreatedDateFrom() && $filter->getCreatedDateTo()) {
            $query[] = new Between("unimportedListing.createdDate", $filter->getCreatedDateFrom(), $filter->getCreatedDateTo());
        }
        if (!empty($filter->getStatus())) {
            $query['unimportedListing.status'] = $filter->getStatus();
        }
        if (!empty($filter->getVariationCount())) {
            $query['unimportedListing.variationCount'] = $filter->getVariationCount();
        }
        if (!is_null($filter->isHidden())) {
            $query['unimportedListing.hidden'] = $filter->isHidden();
        }
        if (!empty($filter->getChannel())) {
            $query['unimportedListing.channel'] = $filter->getChannel();
        }
        if (!empty($filter->getMarketplace())) {
            $query['unimportedListing.marketplace'] = $filter->getMarketplace();
        }

        if (!empty($filter->getSku()) || !empty($filter->getVariationSkus())) {
            // Must do SKU check as (LIKE OR LIKE) instead of IN() otherwise
            // MySQL ignores trailing spaces and we get unexpected results
            $where = $this->arrayFiltersToWhere($query);
            if (!empty($filter->getSku())) {
                $sku = (array)$filter->getSku();
                $this->filterArrayValuesToOrdLikes('unimportedListing.sku', $sku, $where);
            }
            if (!empty($filter->getVariationSkus())) {
                $this->joinToUnimportedListingVariationTable($select);
                $sku = (array)$filter->getVariationSkus();
                $this->filterArrayValuesToOrdLikes('ulvs1.sku', $sku, $where);
            }
            return $where;
        }
        return $query;
    }

    protected function addSearchTermQuery(Select $select, Filter $filter)
    {
        if (empty($filter->getSearchTerm())) {
            return;
        }

        $searchFields = [];
        $searchTerm  = "%" . $filter->getSearchTerm() . "%";

        foreach ($this->getSearchFields() as $table => $fields) {
            foreach ($fields as $field) {
                $searchFields[] = '`' . $table . '`.' . $field . ' LIKE ?';
            }
        }

        $this->joinToUnimportedListingVariationTable($select);
        $select->where(
            [
                "(" . implode(' OR ', $searchFields) . ")" => array_fill(0, count($searchFields), $searchTerm)
            ]
        );
    }

    protected function updateVariationSkus($entity)
    {
        $unimportedListingId = $entity->getId();
        $this->deleteVariationSkus($unimportedListingId);

        $variationSkus = $entity->getVariationSkus();
        if(is_null($variationSkus) || count($variationSkus) === 0) {
            return;
        }
        $this->saveVariationSkus($unimportedListingId, $variationSkus);
    }

    protected function deleteVariationSkus($unimportedListingId)
    {
        $delete = $this->getWriteSql()->delete(static::VARIATION_TABLE);
        $query = [
            'unimportedListingId' => $unimportedListingId,
        ];
        $delete->where($query);
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
    }

    protected function saveVariationSkus($unimportedListingId, $variationSkus)
    {
        if(is_null($variationSkus) || count($variationSkus) === 0) {
            return;
        }
        $variationSkuInsert = $this->getWriteSql()->replace(static::VARIATION_TABLE);
        foreach($variationSkus as $variationSku) {
            $variationSkuInsert->values([
                'unimportedListingId' => $unimportedListingId,
                'sku' => $variationSku
            ]);
            $this->getWriteSql()->prepareStatementForSqlObject($variationSkuInsert)->execute();
        }
    }

    /**
     * @return Select
     */
    protected function getSelect()
    {
        $select = $this->getReadSql()->select('unimportedListing');
        $select
            ->join(
                ['ulvs2' => 'unimportedListingVariation'],
                'ulvs2.unimportedListingId = unimportedListing.id',
                ['variationSkus' => new Expression('IF(unimportedListing.variationCount > 0, GROUP_CONCAT(DISTINCT ulvs2.sku), "")')],
                $select::JOIN_LEFT
            )
            ->group('unimportedListing.id');
        return $select;
    }

    /**
     * @return Insert
     */
    protected function getInsert()
    {
        return $this->getWriteSql()->insert('unimportedListing');
    }

    /**
     * @return Update
     */
    protected function getUpdate()
    {
        return $this->getWriteSql()->update('unimportedListing');
    }

    /**
     * @return Delete
     */
    protected function getDelete()
    {
        return $this->getWriteSql()->delete('unimportedListing');
    }

    public function getEntityClass()
    {
        return Entity::class;
    }

    protected function getSearchFields()
    {
        return $this->searchFields;
    }
}
