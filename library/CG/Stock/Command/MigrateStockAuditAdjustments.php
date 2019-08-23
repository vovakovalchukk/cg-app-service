<?php
namespace CG\Stock\Command;

use CG\CGLib\Command\SignalHandlerTrait;
use CG\Locking\Failure as LockingFailure;
use CG\Locking\Service as LockingService;
use CG\Predis\Command\DecrMin;
use CG\Predis\Command\IncrMax;
use CG\Stats\StatsTrait;
use CG\Stdlib\Date;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stock\Audit\Adjustment\AbortException;
use CG\Stock\Audit\Adjustment\MigrationInterface;
use CG\Stock\Audit\Adjustment\MigrationTimer;
use CG\Stock\Audit\Adjustment\StorageInterface;
use CG\Stock\Locking\Audit\Adjustment\MigrationPeriod;
use Predis\Client as Predis;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateStockAuditAdjustments implements LoggerAwareInterface
{
    use LogTrait;
    use StatsTrait;
    use SignalHandlerTrait;

    protected const PROCESS_COUNT = 'MigrateStockAuditAdjustments';
    protected const MAX_PROCESSES = 3;

    protected const LOG_CODE = 'MigrateStockAuditAdjustments';
    protected const LOG_CODE_MAX_PROCESSES_REACHED = 'The maximum number of processes are already running';
    protected const LOG_MSG_MAX_PROCESSES_REACHED = 'The maximum number of processes are already running';
    protected const LOG_CODE_TIME_FRAME = 'Migrating stock audit adjustments';
    protected const LOG_MSG_TIME_FRAME = 'Migrating stock audit adjustments older than or equal to %s (%s) - limited to %s period%s';
    protected const LOG_CODE_UNSUPPORTED_STORAGE = 'Can not migrate from storage';
    protected const LOG_MSG_UNSUPPORTED_STORAGE = 'Can not migrate from storage';
    protected const LOG_CODE_NO_DATA = 'Found no stock audit adjustments to migrate';
    protected const LOG_MSG_NO_DATA = 'Found no stock audit adjustments to migrate';
    protected const LOG_CODE_FOUND_DATA = 'Found stock audit adjustments to migrate';
    protected const LOG_MSG_FOUND_DATA = 'Found %s stock audit adjustment%s to migrate for %s';
    protected const LOG_CODE_SKIPPED = 'Skipping migration of stock audit adjustments';
    protected const LOG_MSG_SKIPPED = 'Skipping migration of stock audit adjustments for %s: %s';
    protected const LOG_CODE_FAILURE = 'Failed to migrate stock audit adjustments';
    protected const LOG_MSG_FAILURE = 'Failed to migrate stock audit adjustments';
    protected const LOG_CODE_MIGRATED = 'Migrated stock audit adjustments';
    protected const LOG_MSG_MIGRATED = 'Migrated stock audit adjustments';
    protected const LOG_CODE_MIGRATION_TIMINGS = 'Timings';
    protected const LOG_MSG_MIGRATION_TIMINGS = 'Migration completed in %ss';

    protected const STAT_MIGRATION_COUNT = 'stock.audit.adjustment.migration.%s';
    protected const STAT_MIGRATION_TIMING = 'stock.audit.adjustment.migration.%s.%s';

    /** @var StorageInterface|MigrationInterface */
    protected $storage;
    /** @var StorageInterface */
    protected $archive;
    /** @var Predis */
    protected $predis;
    /** @var LockingService */
    protected $lockingService;

    public function __construct(
        StorageInterface $storage,
        StorageInterface $archive,
        Predis $predis,
        LockingService $lockingService
    ) {
        $this->storage = $storage;
        $this->archive = $archive;
        $this->setPredis($predis);
        $this->lockingService = $lockingService;
    }

    protected function setPredis(Predis $predis): void
    {
        $this->predis = $predis;
        $this->predis->getProfile()->defineCommand('incrmax', IncrMax::class);
        $this->predis->getProfile()->defineCommand('decrmin', DecrMin::class);
    }

    public function __invoke(OutputInterface $output, string $timeFrame, int $limit = null)
    {
        try {
            $this->registerSignalHandler(function (int $signal) {
                throw new AbortException(sprintf('Aborting due to %s signal', $this->signalName($signal)));
            }, SIGINT, SIGTERM);

            if (!($locked = $this->predis->incrmax(static::PROCESS_COUNT, static::MAX_PROCESSES))) {
                $this->logDebug(static::LOG_MSG_MAX_PROCESSES_REACHED, [], [static::LOG_CODE, static::LOG_CODE_MAX_PROCESSES_REACHED]);
                $output->writeln(sprintf('<info>%s</info>', static::LOG_MSG_MAX_PROCESSES_REACHED));
                return;
            }

            $this->migrateData($output, $timeFrame, $limit);
        } catch (AbortException $abort) {
            $output->writeln(sprintf('<error>%s</error>', $abort->getMessage()));
        } finally {
            $this->restoreSignalHandler(SIGINT, SIGTERM);
            if ($locked ?? false) {
                $this->predis->decrmin(static::PROCESS_COUNT, 0);
            }
        }
    }

    protected function migrateData(OutputInterface $output, string $timeFrame, int $limit = null)
    {
        $date = $this->restrictDate(new Date((new DateTime($timeFrame))->resetTime()->stdDateFormat()));
        $this->logDebug(static::LOG_MSG_TIME_FRAME, [$timeFrame, 'date' => $date->getDate(), $limit ?? 'unlimited', $limit != 1 ? 's' : ''], [static::LOG_CODE, static::LOG_CODE_TIME_FRAME]);
        $output->writeln(sprintf(static::LOG_MSG_TIME_FRAME, $timeFrame, $date->getDate(), $limit ?? 'unlimited', $limit != 1 ? 's' : ''));

        if (!($this->storage instanceof MigrationInterface)) {
            $exception = new \LogicException(sprintf('Storage does not implement %s', MigrationInterface::class));
            $this->logErrorException($exception, static::LOG_MSG_UNSUPPORTED_STORAGE, [], [static::LOG_CODE, static::LOG_CODE_UNSUPPORTED_STORAGE]);
            $output->writeln(sprintf('<error>%s</error>', static::LOG_MSG_UNSUPPORTED_STORAGE));
            throw $exception;
        }

        $migrationPeriods = $this->storage->fetchMigrationPeriodsWithDataOlderThanOrEqualTo($date, $limit);
        if (empty($migrationPeriods)) {
            $this->logWarning(static::LOG_MSG_NO_DATA, [], [static::LOG_CODE, static::LOG_CODE_NO_DATA]);
            $output->writeln(sprintf('<error>%s</error>', static::LOG_MSG_NO_DATA));
            return;
        }

        try {
            gc_disable();
            gc_collect_cycles();
            gc_mem_caches();

            foreach ($migrationPeriods as $migrationPeriod) {
                try {
                    $this->migrateDate($output, new MigrationPeriod($migrationPeriod));
                } finally {
                    gc_collect_cycles();
                    gc_mem_caches();
                }
            }
        } finally {
            gc_enable();
        }
    }

    protected function restrictDate(Date $date): Date
    {
        $lastWeek = (new DateTime())->resetTime();
        $lastWeek->sub(new \DateInterval(sprintf('P%dD', $lastWeek->format('N'))));
        $lastWeek = new Date($lastWeek->stdDateFormat());
        return $date->diff($lastWeek)->invert ? $lastWeek : $date;
    }

    protected function migrateDate(OutputInterface $output, MigrationPeriod $migrationPeriod)
    {
        try {
            $migrationTimer = new MigrationTimer();
            $totalTimer = $migrationTimer->getTotalTimer();

            try {
                $lock = $this->lockingService->lock($migrationPeriod);
                $loadTimer = $migrationTimer->getLoadTimer();
                $collection = $this->storage->fetchCollectionForMigrationPeriod($migrationPeriod);
                $loadTimer();
            } catch (NotFound|LockingFailure $exception) {
                $this->logDebug(static::LOG_MSG_SKIPPED, ['period' => $migrationPeriod, $exception->getMessage()], [static::LOG_CODE, static::LOG_CODE_SKIPPED]);
                $output->writeln(sprintf(static::LOG_MSG_SKIPPED, $migrationPeriod, sprintf('<error>%s</error>', $exception->getMessage())));
                return;
            }

            $this->logDebug(static::LOG_MSG_FOUND_DATA, [number_format($collection->count()), $collection->count() != 1 ? 's' : '', 'period' => $migrationPeriod], [static::LOG_CODE, static::LOG_CODE_FOUND_DATA]);
            $output->write(sprintf(static::LOG_MSG_FOUND_DATA . '... ', number_format($collection->count()), $collection->count() != 1 ? 's' : '', $migrationPeriod));

            try {
                $this->storage->beginTransaction();
                $this->archive->saveCollection($collection, $migrationTimer);
                $this->storage->removeCollectionForMigrationPeriod($migrationPeriod);
                $this->storage->commitTransaction();
                $this->statsIncrement(static::STAT_MIGRATION_COUNT, [$this->getServerName()], $collection->count());
            } catch (\Throwable $throwable) {
                $this->storage->rollbackTransaction();
                $this->logAlertException($throwable, static::LOG_MSG_FAILURE, [], [static::LOG_CODE, static::LOG_CODE_FAILURE], ['period' => $migrationPeriod]);
                $output->writeln(sprintf('<error>%s</error>', static::LOG_MSG_FAILURE));
                if ($throwable instanceof AbortException) {
                    throw $throwable;
                }
                return;
            } finally {
                $totalTimer();
                $this->logDebug(static::LOG_MSG_MIGRATION_TIMINGS, ['timings.total' => $migrationTimer->getTotal()], [static::LOG_CODE, static::LOG_CODE_MIGRATION_TIMINGS], ['period' => $migrationPeriod, 'timings.load' => $migrationTimer->getLoad(), 'timings.compression' => $migrationTimer->getCompression(), 'timings.upload' => $migrationTimer->getUpload(), 'count' => $collection->count()]);
                $this->statsTiming(static::STAT_MIGRATION_TIMING, $migrationTimer->getLoad(), ['load', $this->getServerName()]);
                $this->statsTiming(static::STAT_MIGRATION_TIMING, $migrationTimer->getCompression(), ['compression', $this->getServerName()]);
                $this->statsTiming(static::STAT_MIGRATION_TIMING, $migrationTimer->getUpload(), ['upload', $this->getServerName()]);
                $this->statsTiming(static::STAT_MIGRATION_TIMING, $migrationTimer->getTotal(), ['total', $this->getServerName()]);
            }

            $this->logDebug(static::LOG_MSG_MIGRATED, [], [static::LOG_CODE, static::LOG_CODE_MIGRATED], ['period' => $migrationPeriod]);
            $output->writeln(sprintf('<info>%s</info>', static::LOG_MSG_MIGRATED));
        } finally {
            if (isset($lock)) {
                $this->lockingService->unlock($lock);
            }
        }
    }
}