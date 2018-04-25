<?php
namespace CG\Product\Category\Template\Storage;

use CG\Product\Category\Template\Collection;
use CG\Product\Category\Template\Entity;
use CG\Product\Category\Template\Filter;
use CG\Product\Category\Template\StorageInterface;
use function CG\Stdlib\escapeLikeValue;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Collection\SaveInterface as SaveCollectionInterface;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Update;

class Db extends DbAbstract implements StorageInterface, SaveCollectionInterface
{
    const TABLE = 'categoryTemplate';
    const TABLE_CATEGORIES = 'categoryTemplateCategory';

    protected $searchFields = [
        self::TABLE . '.name',
    ];

    public function fetch($id)
    {
        /** @var Entity $entity */
        $entity = parent::fetch($id);
        $accountCategoriesArray = $this->fetchAccountCategoriesForEntity($entity);
        $entity->setAccountCategoriesFromRawArray($accountCategoriesArray);
        return $entity;
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $query = $this->buildFilterQuery($filter);
            $select = $this->getSelect()->where($query);
            if (isset($query[static::TABLE_CATEGORIES.'.categoryId'])) {
                $select->join(static::TABLE_CATEGORIES, static::TABLE_CATEGORIES.'.categoryTemplateId = '.static::TABLE.'.id', []);
                $select->group(static::TABLE.'.id');
            }

            if ($filter->getLimit() != 'all') {
                $offset = ($filter->getPage() - 1) * $filter->getLimit();
                $select->limit($filter->getLimit())
                    ->offset($offset);
            }

            /** @var Collection $collection */
            $collection = $this->fetchPaginatedCollection(
                new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray()),
                $this->getReadSql(),
                $select,
                $this->getMapper()
            );
            $accountCategoriesByTemplate = $this->fetchAccountCategoriesForCollection($collection);
            foreach ($accountCategoriesByTemplate as $templateId => $accountCategories) {
                /** @var Entity $template */
                $template = $collection->getById($templateId);
                $template->setAccountCategoriesFromRawArray($accountCategories);
            }
            return $collection;

        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function buildFilterQuery(Filter $filter): array
    {
        $filterArray = $filter->toArray();
        unset($filterArray['limit'], $filterArray['page']);
        $query = array_filter(
            $filterArray,
            function($value): bool {
                return !empty($value);
            }
        );
        if (isset($query['organisationUnitId'])) {
            $query[static::TABLE.'.organisationUnitId'] = $query['organisationUnitId'];
            unset($query['organisationUnitId']);
        }
        if (isset($query[Entity::KEY_CATEGORY_ID])) {
            $query[static::TABLE_CATEGORIES.'.categoryId'] = $query[Entity::KEY_CATEGORY_ID];
            unset($query[Entity::KEY_CATEGORY_ID]);
        }
        if (isset($query['accountId'])) {
            $query[static::TABLE_CATEGORIES.'.accountId'] = $query['accountId'];
            unset($query['accountId']);
        }
        if (isset($query['search'])) {
            $query = array_merge($query, $this->getSearchTermQuery($query['search']));
            unset($query['search']);
        }
        return $query;
    }

    protected function getSearchTermQuery(string $searchTerm): array
    {
        $searchFields = [];
        $searchLike  = "%" . escapeLikeValue($searchTerm) . "%";

        foreach ($this->searchFields as $field) {
            $searchFields[] = $field . ' LIKE ?';
        }

        return [
            "(" . implode(' OR ', $searchFields) . ")" => array_fill(0, count($searchFields), $searchLike)
        ];
    }

    protected function fetchAccountCategoriesForEntity(Entity $entity): array
    {
        $accountCategoriesByTemplate = $this->fetchAccountCategoriesForTemplateIds([$entity->getId()]);
        if (!isset($accountCategoriesByTemplate[$entity->getId()])) {
            return [];
        }
        return $accountCategoriesByTemplate[$entity->getId()];
    }

    protected function fetchAccountCategoriesForCollection(Collection $collection): array
    {
        return $this->fetchAccountCategoriesForTemplateIds($collection->getIds());
    }

    protected function fetchAccountCategoriesForTemplateIds(array $templateIds): array
    {
        $categoryRows = $this->fetchAssociatedCategoryIds($templateIds);
        $accountCategoriesByTemplate = [];
        foreach ($categoryRows as $categoryRow) {
            if (!isset($accountCategoriesByTemplate[$categoryRow['categoryTemplateId']])) {
                $accountCategoriesByTemplate[$categoryRow['categoryTemplateId']] = [];
            }
            $accountCategoriesByTemplate[$categoryRow['categoryTemplateId']][$categoryRow['accountId']] = [
                Entity::KEY_CATEGORY_ID => $categoryRow['categoryId']
            ];
        }
        return $accountCategoriesByTemplate;
    }

    protected function fetchAssociatedCategoryIds(array $templateIds): ResultInterface
    {
        $select = $this->getSelect(static::TABLE_CATEGORIES)->where(['categoryTemplateId' => $templateIds]);
        return $this->getReadSql()->prepareStatementForSqlObject($select)->execute();
    }

    protected function insertEntity($entity)
    {
        parent::insertEntity($entity);
        $this->insertAssociatedCategoryIds($entity);
    }

    protected function insertAssociatedCategoryIds(Entity $entity)
    {
        if (empty($entity->getAccountCategories())) {
            return;
        }
        $insert = $this->getInsert(static::TABLE_CATEGORIES);
        foreach ($entity->getAccountCategories() as $accountCategory) {
            $insert->values(
                [
                    'categoryTemplateId' => $entity->getId(),
                    'categoryId' => $accountCategory->getCategory(),
                    'accountId' => $accountCategory->getAccount(),
                    'organisationUnitId' => $entity->getOrganisationUnitId(),
                ]
            );
            $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        }
    }

    protected function getEntityArray($entity)
    {
        $array = $entity->toArray();
        unset($array['accounts']);
        return $array;
    }

    /**
     * @param Entity $entity
     */
    protected function updateEntity($entity)
    {
        parent::updateEntity($entity);
        $this->deleteAssociatedCategoryIds($entity);
        $this->insertAssociatedCategoryIds($entity);
    }

    protected function deleteAssociatedCategoryIds(Entity $entity)
    {
        $delete = $this->getDelete(static::TABLE_CATEGORIES)->where(['categoryTemplateId' => $entity->getId()]);
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
    }

    public function remove($entity)
    {
        $this->deleteAssociatedCategoryIds($entity);
        parent::remove($entity);
    }

    protected function getSelect(string $table = null): Select
    {
        $table = $table ?? static::TABLE;
        return $this->getReadSql()->select($table);
    }

    protected function getInsert(string $table = null): Insert
    {
        $table = $table ?? static::TABLE;
        return $this->getWriteSql()->insert($table);
    }

    protected function getUpdate(string $table = null): Update
    {
        $table = $table ?? static::TABLE;
        return $this->getWriteSql()->update($table);
    }

    protected function getDelete(string $table = null): Delete
    {
        $table = $table ?? static::TABLE;
        return $this->getWriteSql()->delete($table);
    }

    public function getEntityClass()
    {
        return Entity::class;
    }
}