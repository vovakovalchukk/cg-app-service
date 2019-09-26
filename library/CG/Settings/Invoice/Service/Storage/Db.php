<?php

namespace CG\Settings\Invoice\Service\Storage;


use CG\Settings\Invoice\Shared\Entity as InvoiceEntity;
use CG\Settings\Invoice\Shared\Filter as InvoiceFilter;
use CG\Settings\Invoice\Shared\StorageInterface;
use CG\Stdlib\Coerce\Id\IntegerTrait as CoerceIntegerIdTrait;
use CG\Stdlib\CollectionInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Mapper\FromArrayInterface as ArrayMapper;
use CG\Stdlib\PaginatedCollection as InvoiceCollection;
use CG\Stdlib\Storage\Db\DbAbstract;
use InvalidArgumentException;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Select as ZendSelect;
use Zend\Db\Sql\Sql as ZendSql;
use Zend\Db\Sql\Where;

class Db extends DbAbstract implements StorageInterface
{
    use CoerceIntegerIdTrait;

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
            $select->columns([ new Expression(sprintf('DISTINCT (%s.id) as id', self::TABLE))]);

            if ($filter->getLimit() != 'all') {
                $offset = ($filter->getPage() - 1) * $filter->getLimit();
                $select->limit($filter->getLimit())->offset($offset);
            }

            $collection = new InvoiceCollection($this->getEntityClass(), __FUNCTION__, $filter->toArray());
            $collection->setTotal($this->countResults($this->readSql, $select));

            $idResults = $this->readSql->prepareStatementForSqlObject($select)->execute();
            $ids = [];
            foreach ($idResults as $idResult) {
                $ids[$idResult['id']] = $idResult['id'];
            }

            if (count($idResults) == 0) {
                throw new NotFound();
            }

            $idPredicate = new Predicate();
            $idPredicate->in(self::TABLE . '.id', $ids);
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

    protected function fetchCollection(CollectionInterface $collection, ZendSql $sql, ZendSelect $select, ArrayMapper $arrayMapper, $expected = false)
    {
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        if ($results->count() == 0 || ($expected !== false && $results->count() != $expected)) {
            throw new NotFound();
        }

        $entityData = $this->groupRowsByEntity($results);
        foreach ($entityData as $rows) {
            $collection->attach($arrayMapper->fromArray($rows));
        }

        return $collection;
    }

    protected function groupRowsByEntity(ResultInterface $results): array
    {
        $entityData = [];
        foreach ($results as $row) {
            $id = $row['id'];
            if (!isset($entityData[$id])) {
                $entityData[$id] = [];
            }
            $entityData[$id][] = $row;
        }
        return $entityData;
    }

    protected function saveEntity($entity)
    {
        $entity = $this->saveInvoiceSettings($entity);
        $entity = $this->saveInvoiceSettingsTradingCompanies($entity);
        return $entity;
    }

    protected function saveInvoiceSettingsTradingCompanies($entity)
    {
        $tradingCompanies = $entity->getTradingCompanies();
        $updatedTradingCompanies = [];
        foreach ($tradingCompanies as $tradingCompany) {
            if (isset($tradingCompany['id'])) {
                try {
                    static::coerceId($tradingCompany['id']);
                } catch (InvalidArgumentException $exception) {
                    $tradingCompany['mongoId'] = $tradingCompany['id'];
                    unset($tradingCompany['id']);
                }
            }
            $tradingCompany['invoiceSettingId'] = $entity->getId(false);

            if (isset($tradingCompany['id'])) {
                try {
                    $this->fetchTradingCompany($tradingCompany['id']);
                } catch (NotFound $notFound) {
                    $id = $this->insertTradingCompany($tradingCompany);
                }
                $this->updateTradingCompany($tradingCompany);
            } else {
                $id = $this->insertTradingCompany($tradingCompany);
                $tradingCompany['id'] = $id;

            }

            $updatedTradingCompanies[$tradingCompany['id']] = $tradingCompany;
        }
        $entity->setTradingCompanies($updatedTradingCompanies);
        return $entity;
    }

    protected function saveInvoiceSettings($entity)
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
                    SELF::TABLE . '.mongoId' => $entity->getMongoId()
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

    protected function insertTradingCompany($tradingCompany)
    {
        $insert = $this->getTradingCompanyInsert()->values($tradingCompany);
        $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        $id = $this->getWriteSql()->getAdapter()->getDriver()->getLastGeneratedValue();

        return $id;
    }

    protected function updateTradingCompany($tradingCompany)
    {
        $update = $this->getTradingCompanyUpdate()->set($tradingCompany)
            ->where(array('id' => $tradingCompany['id']));
        $this->getWriteSql()->prepareStatementForSqlObject($update)->execute();
    }

    protected function getEntityArray($entity)
    {
        $entityArray = parent::getEntityArray($entity);
        $entityArray['id'] = $entity->getId(false);
        unset($entityArray['tradingCompanies']);
        if ($mongoId = $entity->getMongoId()) {
            $entityArray['mongoId'] = $mongoId;
        }
        return $entityArray;
    }

    public function fetch($id)
    {
        try {
            return $this->fetchEntity(
                $this->getReadSql(),
                $this->getSelect()->where([
                    SELF::TABLE . '.id' => $id
                ]),
                $this->getMapper()
            );
        } catch (NotFound $exception) {
            return $this->fetchEntity(
                $this->getReadSql(),
                $this->getSelect()->where([
                    SELF::TABLE . '.mongoId' => $id
                ]),
                $this->getMapper()
            );
        }
    }

    public function remove($entity)
    {
        parent::remove($entity);
        $delete = $this->getTradingCompanyDelete()->where(array(
            'invoiceSettingId' => $entity->getId()
        ));
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
    }

    protected function fetchTradingCompany($id)
    {
        $select = $this->getTradingCompanySelect()->where(
            [SELF::CHILD_TABLE . '.id' => $id]
        );
        $statement = $this->readSql->prepareStatementForSqlObject($select);

        $results = $statement->execute();
        if ($results->count() != 1) {
            throw new NotFound('Could not retrieve trading company ', $id);
        }

        return $results->current();
    }

    protected function fetchEntity(ZendSql $sql, ZendSelect $select, ArrayMapper $arrayMapper)
    {
        $statement = $sql->prepareStatementForSqlObject($select);

        $results = $statement->execute();
        if ($results->count() < 1) {
            throw new NotFound();
        }
        $resultsArray = [];
        foreach ($results as $result) {
            $resultsArray[] = $result;
        }

        return $arrayMapper->fromArray($resultsArray);
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

    /** @return ZendSelect */
    protected function getSelect()
    {
        return $this->readSql
            ->select(self::TABLE)
            ->join(
                self::CHILD_TABLE,
                sprintf('%s.id = %s.invoiceSettingId', self::TABLE, self::CHILD_TABLE),
                [
                    'TCid' => 'id',
                    'TCassignedInvoice' => 'assignedInvoice',
                    'TCemailSendAs' => 'emailSendAs',
                    'TCemailVerified' => 'emailVerified',
                    'TCemailVerificationStatus' => 'emailVerificationStatus',
                    'TCinvoiceSettingId' => 'invoiceSettingId',
                    'TCmongoId' => 'mongoId',
                ],
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

    protected function getUpdate()
    {
        return $this->writeSql->update(self::TABLE);
    }

    protected function getTradingCompanyInsert()
    {
        return $this->writeSql->insert(self::CHILD_TABLE);
    }

    protected function getTradingCompanyUpdate()
    {
        return $this->writeSql->update(self::CHILD_TABLE);
    }

    protected function getTradingCompanyDelete()
    {
        return $this->writeSql->delete(self::CHILD_TABLE);
    }

    protected function getTradingCompanySelect()
    {
        return $this->readSql->select(self::CHILD_TABLE);
    }

    protected function getEntityClass()
    {
        return InvoiceEntity::class;
    }
}