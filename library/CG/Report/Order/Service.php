<?php
namespace CG\Report\Order;

use CG\Db\Query;
use CG\Order\Service\Filter;
use CG\Order\Service\Storage\Persistent\Db;
use CG\Report\Order\Dimension\DimensionInterface;
use CG\Report\Order\Dimension\Factory as DimensionFactory;
use CG\Report\Order\Metric\Factory as MetricFactory;
use CG\Report\Order\DateUnit\Service as DateUnitService;

class Service
{
    /** @var Db */
    protected $orderDbService;
    /** @var MetricFactory */
    protected $metricFactory;
    /** @var DimensionFactory */
    protected $dimensionFactory;
    /** @var DateUnitService */
    protected $dateUnitService;

    public function __construct(Db $orderDbService, MetricFactory $metricFactory, DimensionFactory $dimensionFactory, DateUnitService $dateUnitService)
    {
        $this->orderDbService = $orderDbService;
        $this->metricFactory = $metricFactory;
        $this->dimensionFactory = $dimensionFactory;
        $this->dateUnitService = $dateUnitService;
    }

    public function fetch(Filter $filterEntity, string $dimension, array $metrics)
    {
        $where = $this->filterToWhere($filterEntity);
        $query = $this->buildQuery($where, $dimension, $metrics);
        $result = $this->orderDbService->getReadSql()->query($query, $where->getWhereParameters());
    }

    protected function buildQuery(Query\Where $where, string $dimension, array $metrics)
    {
        $dimension = $this->dimensionFactory->getDimension($dimension);
        $query = $this->getSelect($dimension, $metrics)
            . ' WHERE '. $where->getWhere()
            . $this->getGroupBy($dimension);
        return $query;
    }

    protected function filterToWhere(Filter $filterEntity)
    {
        $orderTableName = '`'. $this->orderDbService->getOrderTableName() . '`';
        $where = new Query\Where();
        $where
            ->in($orderTableName.'.id', $this->getColumnType('id'), $filterEntity->getOrderIds())
            ->in($orderTableName.'.organisationUnitId', $this->getColumnType('organisationUnitId'), $filterEntity->getOrganisationUnitId())
            ->in($orderTableName.'.status', $this->getColumnType('status'), $filterEntity->getStatus())
            ->in($orderTableName.'.accountId', $this->getColumnType('accountId'), $filterEntity->getAccountId())
            ->in($orderTableName.'.channel', $this->getColumnType('channel'), $filterEntity->getChannel())
            ->in($orderTableName.'.shippingMethod', $this->getColumnType('shippingMethod'), $filterEntity->getShippingMethod())
            ->in($orderTableName.'.batch', $this->getColumnType('batch'), $filterEntity->getBatch())
            ->range($orderTableName.'.purchaseDate', $this->getColumnType('purchaseDate'), $filterEntity->getPurchaseDateFrom(), $filterEntity->getPurchaseDateTo())
            ->in($orderTableName.'.paymentMethod', $this->getColumnType('paymentMethod'), $filterEntity->getPaymentMethod())
            ->in($orderTableName.'.paymentReference', $this->getColumnType('paymentReference'), $filterEntity->getPaymentReference())
            ->range($orderTableName.'.total', $this->getColumnType('total'), $filterEntity->getTotalFrom(), $filterEntity->getTotalTo())
            ->in($orderTableName.'.currencyCode', $this->getColumnType('currencyCode'), $filterEntity->getCurrencyCode())
            ->in($orderTableName.'.fulfilmentChannel', $this->getColumnType('fulfilmentChannel'), $filterEntity->getFulfilmentChannel())
            ->in($orderTableName.'.rootOrganisationUnitId', $this->getColumnType('rootOrganisationUnitId'), $filterEntity->getRootOrganisationUnitId())
            ->in($orderTableName.'.externalId', $this->getColumnType('externalId'), $filterEntity->getExternalId())
            ->in($orderTableName.'.externalUsername', $this->getColumnType('externalUsername'), $filterEntity->getExternalUsername())
            ->range($orderTableName.'.dispatchDate', $this->getColumnType('dispatchDate'), $filterEntity->getDispatchDateFrom(), $filterEntity->getDispatchDateTo());

        return $where;
    }

    protected function getColumnType(string $column): string
    {
        return $this->orderDbService->getColumnType($column);
    }

    protected function getSelect(DimensionInterface $dimension, array $metrics)
    {
        $select = 'SELECT ' . $this->dateUnitService->getSelect() . ', ' . $dimension->getSelect();
        foreach ($metrics as $metric) {
            $select .= ', ' . $this->metricFactory->getMetric($metric)->getSelect();
        }
        $select .= ' FROM `' . $this->orderDbService->getOrderTableName() . '`';
        return $select;
    }

    protected function getGroupBy(DimensionInterface $dimension)
    {
        return ' GROUP BY ' . $this->dateUnitService->getGroupBy() . ', ' . $dimension->getGroupBy();
    }
}
