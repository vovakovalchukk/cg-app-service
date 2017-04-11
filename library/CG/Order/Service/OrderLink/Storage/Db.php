<?php
namespace CG\Order\Service\OrderLink\Storage;

use CG\Order\Shared\OrderLink\Collection;
use CG\Order\Shared\OrderLink\Entity;
use CG\Order\Shared\OrderLink\Filter;
use CG\Order\Shared\OrderLink\StorageInterface;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Expression;

class Db extends DbAbstract implements StorageInterface
{
    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $query = $this->buildFilterQuery($filter);
            $select = $this->getSelect()->where($query);

            if (!empty($filter->getOrderId())) {
                $select->join(['olo2' => 'orderLinkOrders'], new Expression('orderLink.id = olo2.orderLinkId AND olo2.orderId IN ('."'".implode("','", $filter->getOrderId())."'".')'), []);
            }

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
            $query['orderLink.id'] = $filter->getId();
        }

        return $query;
    }

    public function fetch($id)
    {
        return $this->fetchEntity(
            $this->getReadSql(),
            $this->getSelect()->where(array(
                'orderLink.id' => $id
            )),
            $this->getMapper()
        );
    }

    protected function insertEntity($entity)
    {
        $data = $this->getEntityArray($entity);
        $orderIds = $data['orderIds'];
        unset($data['orderIds']);

        $insert = $this->getInsert()->values($data);
        $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        $id = $this->getWriteSql()->getAdapter()->getDriver()->getLastGeneratedValue();
        $entity->setId($id);
        $entity->setNewlyInserted(true);

        $this->insertOrderLinks($id, $orderIds);
    }

    protected function updateEntity($entity)
    {
        $data = $this->getEntityArray($entity);
        $orderIds = $data['orderIds'];
        unset($data['orderIds']);

        $update = $this->getUpdate()->set($data)
            ->where(array('id' => $entity->getId()));
        $this->getWriteSql()->prepareStatementForSqlObject($update)->execute();

        $this->removeOrderLinks($entity->getId());
        $this->insertOrderLinks($entity->getId(), $orderIds);
    }

    protected function insertOrderLinks($orderLinkId, array $orderIds)
    {
        $ordersInsert = $this->getWriteSql()->insert('orderLinkOrders');
        foreach ($orderIds as $orderId) {
            $ordersInsert->values(['orderLinkId' => $orderLinkId, 'orderId' => $orderId]);
            $this->getWriteSql()->prepareStatementForSqlObject($ordersInsert)->execute();
        }
    }

    public function remove($entity)
    {
        $this->removeOrderLinks($entity->getId());
        parent::remove($entity);
    }

    protected function removeOrderLinks($orderLinkId)
    {
        $deleteOrders = $this->getWriteSql()->delete('orderLinkOrders')
            ->where(['orderLinkId' => $orderLinkId]);
        $this->getWriteSql()->prepareStatementForSqlObject($deleteOrders)->execute();
    }

    protected function getSelect()
    {
        $select = $this->getReadSql()->select('orderLink');
        $select->join('orderLinkOrders', 'orderLink.id = orderLinkOrders.orderLinkId', [
            'orderIds' => new Expression('GROUP_CONCAT(orderLinkOrders.orderId SEPARATOR ",")')
        ])
            ->group('orderLink.id');
        return $select;
    }

    protected function getInsert()
    {
        return $this->getWriteSql()->insert('orderLink');
    }

    protected function getUpdate()
    {
        return $this->getWriteSql()->update('orderLink');
    }

    protected function getDelete()
    {
        return $this->getWriteSql()->delete('orderLink');
    }

    public function getEntityClass()
    {
        return Entity::class;
    }
}