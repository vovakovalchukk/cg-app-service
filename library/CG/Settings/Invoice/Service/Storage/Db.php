<?php

namespace CG\Settings\Invoice\Service\Storage;


use CG\Settings\Invoice\Shared\Entity as InvoiceEntity;
use CG\Settings\Invoice\Shared\Filter as InvoiceFilter;
use CG\Settings\Invoice\Shared\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\PaginatedCollection as InvoiceCollection;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Select as ZendSelect;
use Zend\Db\Sql\Sql as ZendSql;
use Zend\Db\Sql\Where;

class Db extends DbAbstract implements StorageInterface
{
    const TABLE = 'invoiceSetting';
    const CHILD_TABLE = 'invoiceSettingTradingCompany';

    /** @var ZendSql */
    protected $readSql;

    public function fetchCollectionByPagination($limit, $page)
    {
        return $this->fetchCollectionByFilter(
            new InvoiceFilter($limit, $page)
        );
    }

    public function fetchCollectionByFilter(InvoiceFilter $filter)
    {
        try {
            $where = $this->buildFilterQuery($filter);
            $select = $this->getSelect()->where($where);
            $select->columns([sprintf('DISTINCT %s.id', self::TABLE)]);

            if ($filter->getLimit() != 'all') {
                $offset = ($filter->getPage() - 1) * $filter->getLimit();
                $select->limit($filter->getLimit())->offset($offset);
            }

            $collection = new InvoiceCollection($this->getEntityClass(), __FUNCTION__, $filter->toArray());
            $collection->setTotal($this->countResults($this->readSql, $select));

            $idResults = $this->readSql->prepareStatementForSqlObject($select)->execute();
            $ids = [];
            foreach($idResults as $idResult) {
                $ids[] = $idResult->id;
            }

            $idPredicate = new Predicate();
            $idPredicate->in('id', $ids);
            $idBasedSelect = $this->getSelect()->where($idPredicate);

            return $this->fetchCollection($collection, $this->readSql, $idBasedSelect, $this->mapper);

        } catch (ExceptionInterface $exception) {
            throw new StorageException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }

    protected function saveEntity($entity)
    {
        if ($entity->getId(false) != null) {
            try {
                // There are instances where the entity has an ID but that ID does not exist in the database
                $dbEntity = $this->fetch($entity->getId(false));
                // Ensure that the entity's mongo ID is maintained as this is lost as we save
                $entity->setMongoId($dbEntity->getMongoId());
                $this->updateEntity($entity);
            } catch (NotFound $ignored) {
                $this->insertEntity($entity);
            }
            return $entity;
        }

        if (null == $entity->getMongoId()) {
            $this->insertEntity($entity);
            return $entity;
        }

        try {
            $dbEntity = $this->fetchEntity(
                $this->getReadSql(),
                $this->getSelect()->where(array(
                    'mongoId' => $entity->getMongoId()
                )),
                $this->getMapper()
            );

            $entity->setId($dbEntity->getId());
            $this->updateEntity($entity);
        } catch (NotFound $ignored) {
            $this->insertEntity($entity);
        }

        return $entity;
    }

    protected function getEntityArray($entity)
    {
        $entityArray = parent::getEntityArray($entity);
        $entityArray['id'] = $entity->getId(false);
        $entityArray['preference'] = json_encode($entityArray['preference']);
        if ($mongoId = $entity->getMongoId()) {
            $entityArray['mongoId'] = $mongoId;
        }
        return $entityArray;
    }


    public function fetch($id)
    {
        try {
            return parent::fetch($id);
        } catch (NotFound $exception) {
            return $this->fetchEntity(
                $this->getReadSql(),
                $this->getSelect()->where(array(
                    'mongoId' => $id
                )),
                $this->getMapper()
            );
        }
    }

    protected function buildFilterQuery(InvoiceFilter $filter)
    {
        $query = [];
        if ($filter->getEmailSendAs()) {
            $query[self::TABLE . '.emailSendAs'] = $filter->getEmailSendAs();
        }

        if ($filter->getEmailVerified()) {
            $query[self::TABLE . '.emailVerified'] = $filter->getEmailVerified();
        }

        $where = new Where($query);

        if ($filter->getPendingVerification()) {
            $emailUnverifiedPredicate = new Predicate();
            $emailUnverifiedPredicate
                ->equalTo(self::TABLE . '.emailVerified', false)
                ->or->equalTo(self::CHILD_TABLE . '.emailVerified', false);
            $where->andPredicate($emailUnverifiedPredicate);

            $verificationPendingPredicate = new Predicate();
            $verificationPendingPredicate
                ->equalTo(self::TABLE . '.emailVerificationStatus', 'Pending')
                ->or->equalTo(self::CHILD_TABLE . '.emailVerificationStatus', 'Pending');
            $where->andPredicate($verificationPendingPredicate);
        }

        if ($filter->getVerifiedEmail()) {
            $emailVerifiedPredicate = new Predicate();
            $emailVerifiedPredicate
                ->equalTo(self::TABLE . '.emailVerified', true)
                ->or->equalTo(self::CHILD_TABLE . '.emailVerified', true);
            $where->andPredicate($emailVerifiedPredicate);
        }

        return $where;
    }

    protected function getUpdate()
    {
        return $this->writeSql->update(self::TABLE);
    }

    /** @return ZendSelect */
    protected function getSelect()
    {
        return $this->readSql
            ->select(self::TABLE)
            ->join(
                self::CHILD_TABLE,
                sprintf('%s.id = %s.invoiceSettingId', self::TABLE, self::CHILD_TABLE),
                ZendSelect::SQL_STAR,
                ZendSelect::JOIN_LEFT
            );
    }

    protected function getDelete()
    {
        return $this->writeSql->delete(self::TABLE);
    }

    protected function getInsert()
    {
        return $this->writeSql->insert(self::TABLE);
    }

    protected function getEntityClass()
    {
        return InvoiceEntity::class;
    }
}