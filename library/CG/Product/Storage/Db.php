<?php
namespace CG\Product\Storage;

use CG\Product\Collection as ProductCollection;
use CG\Product\Entity as ProductEntity;
use CG\Product\Filter;
use CG\Product\Mapper;
use CG\Product\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Db\ArrayFiltersToWhereTrait;
use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Stdlib\Storage\Db\FilterArrayValuesToOrdLikesTrait;
use CG\Zend\Stdlib\Db\Sql\InsertIgnore as InsertIgnore;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\Expression as Query;
use Zend\Db\Sql\Predicate\In;
use Zend\Db\Sql\Predicate\Like;
use Zend\Db\Sql\Predicate\NotIn;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use function CG\Stdlib\escapeLikeValue;

/**
 * @method Sql getFastReadSql
 * @method Sql getReadSql
 * @method Sql getWriteSql
 */
class Db extends DbAbstract implements StorageInterface
{
    use ArrayFiltersToWhereTrait;
    use FilterArrayValuesToOrdLikesTrait;

    public function __construct(Sql $readSql, Sql $fastReadSql, Sql $writeSql, Mapper $mapper)
    {
        parent::__construct($readSql, $fastReadSql, $writeSql, $mapper);
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $total = $this->fetchEntityCount($filter);
            if ($total == 0) {
                throw new NotFound('No matching products');
            }
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
            $this->appendImages($productCollection);
            return $productCollection;
        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function createSelectFromFilter(Filter $filter)
    {
        $joinWithVariations = false;
        $select = $this->getReadSql()
            ->select('product')
            ->columns(['_id' => 'id'])
            ->where($this->buildFilterQuery($filter));

        $sku = (array) $filter->getSku();
        $typeQuery = $this->buildTypeQuery(array_fill_keys($filter->getType(), true), $joinWithVariations);
        $skuMatchTypeQuery = $this->buildSkuMatchQuery($sku, array_fill_keys($filter->getSkuMatchType(), true));

        if ($searchTerm = $filter->getSearchTerm()) {
            $joinWithVariations = true;
            $select->where($this->buildSearchTermQuery($searchTerm));
        }

        if ($filter->getReplaceVariationWithParent()) {
            $select->columns(
                ['_id' => new Expression('IF(product.parentProductId > 0, product.parentProductId, product.id)')]
            );
        }

        if ($joinWithVariations) {
            $select->join(
                ['variation' => 'product'],
                'variation.parentProductId = product.id AND variation.organisationUnitId = product.organisationUnitId',
                [],
                Select::JOIN_LEFT
            );
        }

        if ($typeQuery->count() > 0) {
            $select->where($typeQuery);
        }

        $select->quantifier(Select::QUANTIFIER_DISTINCT);
        if (empty($sku)) {
            return $select;
        }

        if ($skuMatchTypeQuery->count() > 0) {
            $this->addSkuMatchToSelect(
                $select,
                (array) $filter->getOrganisationUnitId(),
                $sku,
                $skuMatchTypeQuery
            );
        } else {
            $this->filterArrayValuesToOrdLikes('product.sku', $sku, $select->where);
        }

        return $select;
    }

    protected function fetchEntityCount(Filter $filter)
    {
        $select = $this->createSelectFromFilter($filter);
        $quantifier = $select->getRawState(Select::QUANTIFIER);
        $columns = $select->getRawState(Select::COLUMNS);

        $count = $this->getReadSql()
            ->select(['products' => $select])
            ->columns(['count' => new Expression('COUNT(_id)')]);

        $results = $this->getReadSql()->prepareStatementForSqlObject($count)->execute();
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

        $select->order('_id ASC');

        $results = $this->getReadSql()->prepareStatementForSqlObject($select)->execute();
        if($results->count() == 0) {
            throw new NotFound();
        }
        return array_column(iterator_to_array($results), '_id');
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

    protected function buildTypeQuery(array $types, &$joinWithVariations = false)
    {
        $typeQuery = new Predicate(null, Predicate::OP_OR);

        if (isset($types[Filter::TYPE_SIMPLE])) {
            $joinWithVariations = true;
            $typeQuery->addPredicate(
                (new Predicate(null, Predicate::OP_AND))->equalTo('product.parentProductId', 0)->isNull('variation.id')
            );
        }

        if (isset($types[Filter::TYPE_PARENT])) {
            $joinWithVariations = true;
            $typeQuery->addPredicate(
                (new Predicate(null, Predicate::OP_AND))->equalTo('product.parentProductId', 0)->isNotNull('variation.id')
            );
        }

        if (isset($types[Filter::TYPE_VARIATION])) {
            $typeQuery->notEqualTo('product.parentProductId', 0);
        }

        return $typeQuery;
    }

    protected function buildSkuMatchQuery(array $sku, array $skuMatchType)
    {
        $skuMatchTypeQuery = new Predicate(null, Predicate::OP_OR);
        if (empty($sku)) {
            return $skuMatchTypeQuery;
        }

        if (isset($skuMatchType[Filter::SKU_MATCH_ANY])) {
            $skuMatchTypeQuery->addPredicate(
                new Query('skuMatch > 0')
            );
        }

        if (isset($skuMatchType[Filter::SKU_MATCH_ALL])) {
            $skuMatchTypeQuery->addPredicate(
                new Query('(skuMatch = ? AND skuCount = skuMatch)', count($sku))
            );
        }

        if (isset($skuMatchType[Filter::SKU_MATCH_SUPERSET])) {
            $skuMatchTypeQuery->addPredicate(
                new Query('(skuMatch = ? AND skuCount > skuMatch)', count($sku))
            );
        }

        if (isset($skuMatchType[Filter::SKU_MATCH_SUBSET])) {
            $skuMatchTypeQuery->addPredicate(
                new Query('(skuMatch > 0 AND skuMatch < ? AND skuCount = skuMatch)', count($sku))
            );
        }

        return $skuMatchTypeQuery;
    }

    public function addSkuMatchToSelect(Select $select, array $ouId, array $sku, Predicate $skuMatchTypeQuery)
    {
        $simpleSkuMatch = $this->getReadSql()
            ->select(['simple' => 'product'])
            ->columns(['id', 'sku'])
            ->join(
                ['variation' => 'product'],
                'variation.parentProductId = simple.id AND variation.organisationUnitId = simple.organisationUnitId',
                [],
                Select::JOIN_LEFT
            )
            ->where([
                'variation.id' => null,
                new Predicate(array_map(function($sku) {
                    return new Like('simple.sku', escapeLikeValue($sku));
                }, array_values($sku)), Predicate::COMBINED_BY_OR),
            ]);

        $variationSkuMatch = $this->getReadSql()
            ->select(['parent' => 'product'])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(['id'])
            ->join(
                ['lookup' => 'product'],
                'lookup.parentProductId = parent.id AND lookup.organisationUnitId = parent.organisationUnitId',
                []
            )->join(
                ['variation' => 'product'],
                'variation.parentProductId = parent.id AND variation.organisationUnitId = parent.organisationUnitId',
                ['sku']
            )
            ->where([
                'parent.parentProductId' => 0,
                new Predicate(array_map(function($sku) {
                    return new Like('lookup.sku', escapeLikeValue($sku));
                }, array_values($sku)), Predicate::COMBINED_BY_OR),
            ]);

        if (!empty($ouId)) {
            $simpleSkuMatch->where(['simple.organisationUnitId' => array_values($ouId)]);
            $variationSkuMatch->where(['parent.organisationUnitId' => array_values($ouId)]);
        }

        $column = 'skuMatch.sku';
        $skuSelect = implode(' OR ', array_map(function($sku) use($column) {
            return sprintf('%s LIKE "%s"', $column, escapeLikeValue($sku));
        }, $sku));

        $skuMatch = $this->getReadSql()
            ->select(['skuMatch' => $simpleSkuMatch->combine($variationSkuMatch, Select::COMBINE_UNION, Select::QUANTIFIER_DISTINCT)])
            ->columns([
                'id' => 'id',
                'skuMatch' => new Expression(sprintf('SUM(IF(%s, 1, 0))', $skuSelect)),
                'skuCount' => new Expression(sprintf('COUNT(%s)', $column)),
            ])
            ->group('id');

        return $select->join(
            ['skuMatch' => $skuMatch],
            (new Predicate([
                new Query('product.id = skuMatch.id'),
                $skuMatchTypeQuery,
            ])),
            []
        );
    }

    public function fetch($id)
    {
        $select = $this->getSelect()->where(['product.id' => $id]);
        $products = $this->fetchCollectionWithJoinQuery(new ProductCollection($this->getEntityClass(), __FUNCTION__), $select);
        $this->appendImages($products);
        return $products->getById($id);
    }

    public function appendImages(ProductCollection $collection)
    {
        if ($collection->count() == 0) {
            return;
        }

        $select = $this->getImageSelect($collection->getIds());
        $results = $this->getReadSql()->prepareStatementForSqlObject($select)->execute();
        if ($results->count() == 0) {
            return;
        }

        $productImages = [];
        $productListingImages = [];

        foreach ($results as $result) {
            if (!isset($productImages[$result['productId']])) {
                $productImages[$result['productId']] = [];
            }

            if (!isset($productListingImages[$result['productId']])) {
                $productListingImages[$result['productId']] = [];
            }

            if (!isset($result['listingId'])) {
                $productImages[$result['productId']][$result['order']] = [
                    'id' => $result['imageId'],
                    'order' => $result['order'],
                ];
                continue;
            }

            $key = $result['listingId'] . '-' . $result['order'];
            $productListingImages[$result['productId']][$key] = [
                'id' => $result['imageId'],
                'listingId' => $result['listingId'],
                'order' => $result['order'],
            ];
        }

        /** @var ProductEntity $product */
        foreach ($collection as $product) {
            $productId = $product->getId();
            if (isset($productImages[$productId])) {
                $product->setImageIds(array_values($productImages[$productId]));
            }
            if (isset($productListingImages[$productId])) {
                $product->setImageListingIds(array_values($productListingImages[$productId]));
            }
        }
    }

    protected function getImageSelect(array $productIds)
    {
        $productImages = $this->getReadSql()
            ->select('productImage')
            ->columns(['productId' => 'productId', 'listingId' => null, 'imageId' => 'imageId', 'order' => 'order'])
            ->where(['productId' => $productIds]);

        $productListingImages = $this->getReadSql()
            ->select('productListingImage')
            ->columns(['productId' => 'productId', 'listingId' => 'listingId', 'imageId' => 'imageId', 'order' => 'order'])
            ->where(['productId' => $productIds]);

        return $productImages->combine($productListingImages);
    }

    /**
     * @param ProductEntity $entity
     */
    protected function insertEntity($entity)
    {
        $entityArray = $entity->toArray();
        unset(
            $entityArray['attributeNames'],
            $entityArray['attributeValues'],
            $entityArray['imageIds'],
            $entityArray['listingImageIds'],
            $entityArray['taxRateIds']
        );

        $insert = $this->getInsert()->values($entityArray);
        $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        $id = $this->getWriteSql()->getAdapter()->getDriver()->getLastGeneratedValue();

        $entity->setId($id);
        $entity->setNewlyInserted(true);

        $this->saveAttributeRelation($entity);
        $this->saveImageRelation($entity);
        $this->saveTaxRates($entity);
        $this->savePickingLocations($entity);

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
        unset(
            $entityArray['attributeNames'],
            $entityArray['attributeValues'],
            $entityArray['imageIds'],
            $entityArray['listingImageIds'],
            $entityArray['taxRateIds'],
            $entityArray['pickingLocations']
        );

        $update = $this->getUpdate()->set($entityArray)
            ->where(array('id' => $entity->getId()));
        $this->getWriteSql()->prepareStatementForSqlObject($update)->execute();

        $this->removeAttributeRelation($entity);
        $this->saveAttributeRelation($entity);
        $this->saveImageRelation($entity);
        $this->saveTaxRates($entity);
        $this->savePickingLocations($entity);

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
        $productImageDelete = $this->getWriteSql()->delete('productImage')->where(['productId' => $entity->getId()]);
        $this->getWriteSql()->prepareStatementForSqlObject($productImageDelete)->execute();

        $productImageInsert = $this->getWriteSql()->insert('productImage');
        foreach ($entity->getImageIds() as $image) {
            $productImageInsert->values([
                'productId' => $entity->getId(),
                'imageId' => $image['id'],
                'order' => $image['order'],
            ]);
            $this->getWriteSql()->prepareStatementForSqlObject($productImageInsert)->execute();
        }

        $productListingImageDelete = $this->getWriteSql()->delete('productListingImage')->where(['productId' => $entity->getId()]);
        $this->getWriteSql()->prepareStatementForSqlObject($productListingImageDelete)->execute();

        $productListingImageInsert = $this->getWriteSql()->insert('productListingImage');
        foreach ($entity->getImageListingIds() as $image) {
            $productListingImageInsert->values([
                'productId' => $entity->getId(),
                'listingId' => $image['listingId'],
                'imageId' => $image['id'],
                'order' => $image['order'],
            ]);
            $this->getWriteSql()->prepareStatementForSqlObject($productListingImageInsert)->execute();
        }
    }

    protected function saveTaxRates(ProductEntity $entity)
    {
        $productTaxRateDelete = $this->getWriteSql()->delete('productTaxRate');
        $query = [
            'productId' => $entity->getId(),
        ];
        $productTaxRateDelete->where($query);

        $productTaxRateInsert = $this->getWriteSql()->insert('productTaxRate');

        $this->beginTransaction();
        $this->getWriteSql()->prepareStatementForSqlObject($productTaxRateDelete)->execute();
        foreach ($entity->getTaxRateIds() as $VATCountryCode => $taxRateId) {
            $productTaxRateInsert->values([
                'VATCountryCode' => $VATCountryCode,
                'taxRateId' => $taxRateId,
                'productId' => $entity->getId()
            ]);
            $this->getWriteSql()->prepareStatementForSqlObject($productTaxRateInsert)->execute();
        }
        $this->commitTransaction();
    }

    protected function savePickingLocations(ProductEntity $entity)
    {
        $this->beginTransaction();

        $delete = $this->getWriteSql()->delete('productPickingLocation')->where(['productId' => $entity->getId()]);
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();

        foreach ($entity->getPickingLocations() as $level => $pickingLocation) {
            $insert = $this->getWriteSql()->insert('productPickingLocation')->values([
                'productId' => $entity->getId(),
                'level' => $level,
                'name' => $pickingLocation,
            ]);
            $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        }

        $this->commitTransaction();
    }

    /**
     * @return Select
     */
    protected function getSelect()
    {
        return $this->getReadSql()->select('product')
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
            )->join(
                'productTaxRate',
                'productTaxRate.productId = product.id',
                ['taxRateId', 'VATCountryCode'],
                Select::JOIN_LEFT
            )
            ->join(
                'productPickingLocation',
                'productPickingLocation.productId = product.id',
                ['pickingLocationLevel' => 'level', 'pickingLocationName' => 'name'],
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
