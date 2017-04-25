<?php
namespace CG\Settings\InvoiceMapping\Storage;

use CG\Settings\InvoiceMapping\Collection;
use CG\Settings\InvoiceMapping\Entity;
use CG\Settings\InvoiceMapping\Filter;
use CG\Settings\InvoiceMapping\Mapper;
use CG\Settings\InvoiceMapping\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Stdlib\Storage\Db\DeadlockAwareSaveTrait;
use CG\Stdlib\Storage\Db\Zend\Sql as SqlStorage;
use CG\Stdlib\Storage\Db\Zend\TransactionTrait;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Where;

class Db extends DbAbstract implements StorageInterface
{
    use LogTrait;
    use SqlStorage;
    use DeadlockAwareSaveTrait;
    use TransactionTrait;

    const TABLE = 'invoiceMapping';

    /** @var Sql $readSql */
    protected $readSql;
    /** @var Sql $fastReadSql */
    protected $fastReadSql;
    /** @var Sql $writeSql */
    protected $writeSql;
    /** @var Mapper $mapper */
    protected $mapper;

    public function __construct(Sql $readSql, Sql $fastReadSql, Sql $writeSql, Mapper $mapper)
    {
        parent::__construct($readSql, $fastReadSql, $writeSql, $mapper);
    }

    /**
     * @return Entity
     */
    public function fetch($id)
    {
        list($accountId, $site) = array_pad(explode('-', $id, 2), 2, '');
        return $this->fetchEntity(
            $this->readSql,
            $this->getSelect()->where(['accountId' => $accountId, 'site' => $site]),
            $this->mapper
        );
    }

    /**
     * @param Entity $entity
     * @return Entity
     */
    protected function saveEntity($entity)
    {
        try {
            $this->fetch($entity->getId());
            return $this->updateEntity($entity);
        } catch (NotFound $exception) {
            return $this->insertEntity($entity);
        }
    }

    /**
     * @return Collection
     */
    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            return $this->fetchPaginatedCollection(
                new Collection(Entity::class, __FUNCTION__, $filter->toArray()),
                $this->readSql,
                $this->filterQuery($this->getSelect(), $filter),
                $this->mapper
            );
        } catch (ExceptionInterface $exception) {
            throw new StorageException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }

    protected function filterQuery(Select $select, Filter $filter)
    {
        if (!empty($filter->getId())) {
            $where = new Where(null, Where::COMBINED_BY_OR);
            foreach ($filter->getId() as $id) {
                list($accountId, $site) = array_pad(explode('-', $id, 2), 2, '');
                $where->addPredicate(new Where(['accountId' => $accountId, 'site' => $site]));
            }
            $select->where($where);
        }

        if (!empty($filter->getAccountId())) {
            $select->where(['accountId' => $filter->getAccountId()]);
        }

        if (!empty($filter->getOrganisationUnitId())) {
            $select->where(['organisationUnitId' => $filter->getOrganisationUnitId()]);
        }

        if (!empty($filter->getSite())) {
            $select->where(['site' => $filter->getSite()]);
        }

        if ($filter->getLimit() != 'all') {
            $offset = ($filter->getPage() - 1) * $filter->getLimit();
            $select->limit($filter->getLimit())->offset($offset);
        }

        return $select;
    }

    /**
     * @param Entity $entity
     */
    protected function insertEntity($entity)
    {
        $data = $entity->toArray(); unset($data['id']);
        $insert = $this->getInsert()->values($data);
        $this->writeSql->prepareStatementForSqlObject($insert)->execute();
        return $entity->setNewlyInserted(true);
    }

    /**
     * @param Entity $entity
     */
    protected function updateEntity($entity)
    {
        $data = $entity->toArray(); unset($data['id']);
        $update = $this->getUpdate()->set($data)->where(['accountId' => $entity->getAccountId(), 'site' => $entity->getSite()]);
        $this->getWriteSql()->prepareStatementForSqlObject($update)->execute();
        return $entity;
    }

    /**
     * @return Select
     */
    protected function getSelect()
    {
        return $this->readSql->select(static::TABLE);
    }

    /**
     * @return Insert
     */
    protected function getInsert()
    {
        return $this->writeSql->insert(static::TABLE);
    }

    /**
     * @return Update
     */
    protected function getUpdate()
    {
        return $this->writeSql->update(static::TABLE);
    }

    /**
     * @return Delete
     */
    protected function getDelete()
    {
        return $this->writeSql->delete(static::TABLE);
    }
}
