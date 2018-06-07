<?php
namespace CG\Settings\Vat\Storage;

use CG\Settings\Vat\Collection;
use CG\Settings\Vat\Entity as VatSettings;
use CG\Settings\Vat\Filter as Filter;
use CG\Settings\Vat\StorageInterface;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Exception\ExceptionInterface;

class Db extends DbAbstract implements StorageInterface
{
    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $query = $this->buildFilterQuery($filter);
            $select = $this->getSelect()->where($query);

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

    protected function buildFilterQuery(Filter $filter)
    {
        $query = [];
        if (!empty($filter->getId())) {
            $query['vatSettings.id'] = $filter->getId();
        }
        if (!empty($filter->getChargeVat())) {
            $query['vatSettings.chargeVat'] = $filter->getChargeVat();
        }
        return $query;
    }

    protected function getSelect()
    {
        return $this->getReadSql()->select('vatSettings');
    }

    protected function getInsert()
    {
        return $this->getWriteSql()->insert('vatSettings');
    }

    protected function getUpdate()
    {
        return $this->getWriteSql()->update('vatSettings');
    }

    protected function getDelete()
    {
        return $this->getWriteSql()->delete('vatSettings');
    }

    public function getEntityClass()
    {
        return VatSettings::class;
    }
}