<?php
namespace CG\Report\Order\DateUnit;

use CG\Stdlib\DateTime;

class Service
{
    const UNIT = 'dateUnit';
    const UNIT_DAY = 'day';
    const UNIT_WEEK = 'week';
    const UNIT_MONTH = 'month';
    const UNIT_YEAR = 'year';
    const UNIT_INTERVAL = 'interval';

    protected const SELECT = 'select';
    protected const GROUP_BY = 'groupBy';
    protected const UNIT_DIFF_FORMAT = 'diffFormat';

    protected const UNIT_MAP = [
        self::UNIT_DAY => [
            self::SELECT => 'DATE_FORMAT(purchaseDate, \'%Y-%m-%d\') as ' . self::UNIT,
            self::GROUP_BY => 'DATE_FORMAT(purchaseDate, \'%Y-%m-%d\')',
            self::UNIT_DIFF_FORMAT => '%a',
            self::UNIT_INTERVAL => 'P1D'
        ],
        self::UNIT_WEEK => [
            self::SELECT => 'DATE_FORMAT(DATE_ADD(purchaseDate, INTERVAL(-WEEKDAY(purchaseDate)) DAY), \'%Y-%m-%d\') ' . self::UNIT,
            self::GROUP_BY => 'WEEKOFYEAR(purchaseDate)',
            self::UNIT_DIFF_FORMAT => [self::class, 'getWeeksDiff'],
            self::UNIT_INTERVAL => 'P1W'
        ],
        self::UNIT_MONTH => [
            self::SELECT => 'DATE_FORMAT(purchaseDate, \'%Y-%m\') as ' . self::UNIT,
            self::GROUP_BY => 'DATE_FORMAT(purchaseDate, \'%Y-%m\')',
            self::UNIT_DIFF_FORMAT => [self::class, 'getMonthsDiff'],
            self::UNIT_INTERVAL => 'P1M'
        ],
        self::UNIT_YEAR => [
            self::SELECT => 'DATE_FORMAT(DATE_ADD(purchaseDate, INTERVAL(-MONTH(purchaseDate)+1) MONTH), \'%Y-%m\') as ' . self::UNIT,
            self::GROUP_BY => 'DATE_FORMAT(purchaseDate, \'%Y\')',
            self::UNIT_DIFF_FORMAT => '%y',
            self::UNIT_INTERVAL => 'P1Y'
        ]
    ];

    protected const UNIT_CONVERSION_MAP = [
        self::UNIT_DAY => DateTime::FORMAT_DATE,
        self::UNIT_WEEK => [self::class, 'formatWeek'],
        self::UNIT_MONTH => 'Y-m',
        self::UNIT_YEAR => [self::class, 'formatYear']
    ];

    public static function getMonthsDiff(\DateInterval $dateInterval)
    {
        return 12 * $dateInterval->y + $dateInterval->m;
    }

    public static function getWeeksDiff(\DateInterval $dateInterval)
    {
        return $dateInterval->days / 7;
    }

    public static function formatWeek(DateTime $dateTime)
    {
        $time = strtotime('last monday', ($dateTime->add(new \DateInterval('P1D')))->getTimestamp());
        return (new DateTime())
            ->setTimestamp($time)
            ->format(DateTime::FORMAT_DATE);
    }

    public static function formatYear(DateTime $dateTime)
    {
        return $dateTime->setMonth('01')->format('Y-m');
    }

    public function buildStrategyByLimit(DateTime $from, DateTime $to, int $limit): StrategyInterface
    {
        // Try to figure what what is the first unit from the map that has less units than the limit
        $dateInterval = $to->diff($from);
        foreach (self::UNIT_MAP as $unit => [
            self::SELECT => $select,
            self::GROUP_BY => $groupBy,
            self::UNIT_DIFF_FORMAT => $diffFormat,
            self::UNIT_INTERVAL => $interval
        ]) {
            if ($this->getUnitCountFromFormat($dateInterval, $diffFormat) < $limit) {
                return $this->buildStrategy($unit, $select, $groupBy, $interval);
            }
        }

        // Return month as default value
        return $this->buildStrategy(
            self::UNIT_MONTH,
            self::UNIT_MAP[self::UNIT_MONTH][self::SELECT],
            self::UNIT_MAP[self::UNIT_MONTH][self::GROUP_BY],
            self::UNIT_MAP[self::UNIT_MONTH][self::UNIT_INTERVAL]
        );
    }

    public function createZeroFilledArray(StrategyInterface $strategy, DateTime $from, DateTime $to, array $keys)
    {
        $period = new \DatePeriod(
            $from,
            new \DateInterval($strategy->getInterval()),
            $to
        );

        $result = [];
        foreach ($period as $dateTime) {
            $dateUnit = $this->formatUnit($dateTime, $strategy->getType());
            foreach ($keys as $key) {
                $result[$dateUnit][$key] = 0;
            }
        }

        return $result;
    }

    protected function buildStrategy(string $unit, string $select, string $groupBy, string $interval): StrategyInterface
    {
        return new Strategy($unit, $select, $groupBy, $interval);
    }

    protected function getUnitCountFromFormat(\DateInterval $interval, $format)
    {
        if (is_callable($format)) {
            return intval($format($interval));
        }
        return intval($interval->format($format));
    }

    protected function formatUnit(DateTime $dateTime, string $unit)
    {
        $unitFormat = self::UNIT_CONVERSION_MAP[$unit];
        if (is_callable($unitFormat)) {
            return $unitFormat($dateTime);
        }

        return $dateTime->format($unitFormat);
    }
}
