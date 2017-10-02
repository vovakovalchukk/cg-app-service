<?php
namespace CG\Reporting\Order\Storage;

use CG\Db\Query;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Service\Storage\Persistent\Db as OrderDbService;
use CG\Reporting\Order\Collection;
use CG\Reporting\Order\DateUnit\Service as DateUnitService;
use CG\Reporting\Order\DateUnit\StrategyInterface;
use CG\Reporting\Order\Dimension\DimensionInterface;
use CG\Reporting\Order\Dimension\Factory as DimensionFactory;
use CG\Reporting\Order\Filter;
use CG\Reporting\Order\Mapper;
use CG\Reporting\Order\Metric\Factory as MetricFactory;
use CG\Reporting\Order\Metric\MetricInterface;
use CG\Reporting\Order\StorageInterface;
use CG\Stdlib\DateTime;

class Db implements StorageInterface
{
    const ORDER_TABLE = 'order';

    /** @var OrderDbService */
    protected $orderDbService;
    /** @var MetricFactory */
    protected $metricFactory;
    /** @var DimensionFactory */
    protected $dimensionFactory;
    /** @var DateUnitService */
    protected $dateUnitService;
    /** @var Mapper */
    protected $mapper;

    public function __construct(
        OrderDbService $orderDbService,
        MetricFactory $metricFactory,
        DimensionFactory $dimensionFactory,
        DateUnitService $dateUnitService,
        Mapper $mapper
    ) {
        $this->orderDbService = $orderDbService;
        $this->metricFactory = $metricFactory;
        $this->dimensionFactory = $dimensionFactory;
        $this->dateUnitService = $dateUnitService;
        $this->mapper = $mapper;
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        [$start, $end] = $this->getDatesByFilter($filter->getOrderFilter());
        $metricCollection = $this->buildMetricObjectsFromArray($filter->getMetrics());
        $unitStrategy = $this->dateUnitService->buildStrategyByLimit(
            $start,
            $end,
            intval($filter->getOrderFilter()->getLimit())
        );
        $dimension = $this->dimensionFactory->getDimension($filter->getDimension());

        $where = $this->filterToWhere($filter->getOrderFilter());
        $query = $this->buildQuery($where, $unitStrategy, $dimension, $metricCollection);

        $result = $this->orderDbService->getReadSql()->query($query, $where->getWhereParameters());
        $arrayResult = $this->processResults($result, $unitStrategy, $dimension, $metricCollection);
        return $this->buildCollectionFromArray($arrayResult, $filter->getDimension());
    }

    public function buildDatesFromQueryResult(\mysqli_result $result)
    {
        $dates = $result->fetch_assoc();
        return [
            new DateTime($dates['start']),
            new DateTime($dates['end'])
        ];
    }

    protected function buildCollectionFromArray(array $series, string $dimension)
    {
        $collection = new Collection($dimension);
        foreach ($series as $data) {
            $entity = $this->mapper->fromArray($data);
            $collection->attach($entity);
        }
        return $collection;
    }

    protected function processResults(
        array $result,
        StrategyInterface $strategy,
        DimensionInterface $dimension,
        \SplObjectStorage $metrics
    ) {
        $metricKeys = $this->buildMetricKeys($metrics);
        $dimensionValues = $this->getDimensionValuesFromResult($dimension, $result);

        $response = [];
        foreach ($dimensionValues as $dimensionValue) {
            [$start, $end] = $this->getStartEndDateByDimension($result, $dimension->getKey(), $dimensionValue);
            $response[$dimensionValue] = $this->dateUnitService->createZeroFilledArray($strategy, $start, $end, $metricKeys);
        }

        foreach ($result as $row) {
            foreach ($metricKeys as $metricKey) {
                $response[$row[$dimension->getKey()]][$row[DateUnitService::UNIT]][$metricKey] = $row[$metricKey];
            }
        }

        $response = $this->addTotalToResponse($response);
        return $this->formatResponseAsArray($response);
    }

    protected function addTotalToResponse(array $response): array
    {
        $total = [];
        foreach ($response as $dimensionValue => $data) {
            foreach ($data as $dateUnit => $values) {
                foreach ($values as $metricValue => $value) {
                    $current = isset($total[$dateUnit][$metricValue]) ? $total[$dateUnit][$metricValue] : 0;
                    $newValue = $response[$dimensionValue][$dateUnit][$metricValue];
                    $total[$dateUnit][$metricValue] = $current + $newValue;
                }
            }
        }
        $response['total'] = $total;
        return $response;
    }

    protected function formatResponseAsArray(array $response): array
    {
        $array = [];
        foreach ($response as $dimensionValue => $data) {
            $array[] = [
                'name' => $dimensionValue,
                'values' => $data
            ];
        }
        return $array;
    }

    protected function buildMetricObjectsFromArray(array $metrics): \SplObjectStorage
    {
        $collection = new \SplObjectStorage();
        foreach ($metrics as $metric) {
            $collection->attach($this->metricFactory->getMetric($metric));
        }
        return $collection;
    }

    protected function buildMetricKeys(\SplObjectStorage $metrics)
    {
        $keys = [];
        /** @var MetricInterface $metric */
        foreach ($metrics as $metric) {
            $keys[$metric->getKey()] = $metric->getKey();
        }
        return $keys;
    }

    protected function getDimensionValuesFromResult(DimensionInterface $dimension, array $result)
    {
        $dimensionKeys = [];
        foreach ($result as $row) {
            $key = $row[$dimension->getKey()];
            if (!isset($dimensionKeys[$key])) {
                $dimensionKeys[$key] = $key;
            }
        }
        return $dimensionKeys;
    }

    protected function getStartEndDateByDimension(array $result, string $dimensionKey, string $dimensionValue)
    {
        $start = null;
        $end = null;
        foreach ($result as $row) {
            if (!$start && isset($row[$dimensionKey]) && $row[$dimensionKey] == $dimensionValue) {
                $start = $row[DateUnitService::UNIT];
            }
            $end = $row[DateUnitService::UNIT];
        }
        return [new DateTime($start), new DateTime($end)];
    }

    protected function buildQuery(
        Query\Where $where,
        StrategyInterface $unitStrategy,
        DimensionInterface $dimension,
        \SplObjectStorage $metrics
    ) {
        $query = $this->getSelect($unitStrategy, $dimension, $metrics)
            . ' WHERE '. $where->getWhere()
            . $this->getGroupBy($unitStrategy, $dimension)
            . $this->getOrderBy();
        return $query;
    }

    protected function filterToWhere(OrderFilter $filterEntity)
    {
        $orderTableName = '`'. self::ORDER_TABLE . '`';
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

    protected function getSelect(StrategyInterface $strategy, DimensionInterface $dimension, \SplObjectStorage $metrics)
    {
        $select = 'SELECT ' . $strategy->getSelect() . ', ' . $dimension->getSelect();
        foreach ($metrics as $metric) {
            $select .= ', ' . $metric->getSelect();
        }
        $select .= ' FROM `' . self::ORDER_TABLE . '`';
        return $select;
    }

    protected function getGroupBy(StrategyInterface $strategy, DimensionInterface $dimension)
    {
        return ' GROUP BY ' . $strategy->getGroupBy() . ', ' . $dimension->getGroupBy();
    }

    protected function getOrderBy()
    {
        return ' ORDER BY `purchaseDate` ASC';
    }

    protected function getDatesByFilter(OrderFilter $filter)
    {
        if ($filter->getPurchaseDateFrom() && $filter->getPurchaseDateTo()) {
            return [
                new DateTime($filter->getPurchaseDateFrom()),
                new DateTime($filter->getPurchaseDateTo())
            ];
        }

        return $this->getStartEndDatesFromQuery($this->filterToWhere($filter));
    }

    protected function getStartEndDatesFromQuery(Query\Where $where)
    {
        $query = $this->buildQueryForDates($where);
        return $this->orderDbService->getReadSql()->query(
            $query,
            $where->getWhereParameters(),
            [$this, 'buildDatesFromQueryResult']
        );
    }

    protected function buildQueryForDates(Query\Where $where)
    {
        return 'SELECT MIN(purchaseDate) as start, MAX(purchaseDate) as end'
            . ' FROM `' . self::ORDER_TABLE . '`'
            . ' WHERE ' . $where->getWhere();
    }

}
