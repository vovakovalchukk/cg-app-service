<?php
namespace CG\Stock\Command;

use CG\Locking\Failure as LockingFailure;
use CG\Locking\Service as LockingService;
use CG\Stdlib\Date;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stock\Audit\Adjustment\MigrationInterface;
use CG\Stock\Audit\Adjustment\MigrationTimer;
use CG\Stock\Audit\Adjustment\StorageInterface;
use CG\Stock\Locking\Audit\Adjustment\MigrationPeriod;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateStockAuditAdjustments implements LoggerAwareInterface
{
    use LogTrait;

    protected const LOG_CODE = 'MigrateStockAuditAdjustments';
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

    /** @var StorageInterface|MigrationInterface */
    protected $storage;
    /** @var StorageInterface */
    protected $archive;
    /** @var LockingService */
    protected $lockingService;

    public function __construct(StorageInterface $storage, StorageInterface $archive, LockingService $lockingService)
    {
        $this->storage = $storage;
        $this->archive = $archive;
        $this->lockingService = $lockingService;
    }

    public function __invoke(OutputInterface $output, string $timeFrame, int $limit = null)
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

        $this->flushLogs();
        foreach ($migrationPeriods as $migrationPeriod) {
            $this->migrateDate($output, $migrationPeriod);
            $this->flushLogs();
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
        $migrationTimer = new MigrationTimer();
        $totalTimer = $migrationTimer->getTotalTimer();

        try {
            $collection = $this->storage->fetchCollectionForMigrationPeriod($migrationPeriod);
            $lock = $this->lockingService->lock($migrationPeriod);
            $loadTimer = $migrationTimer->getLoadTimer();
            $loadTimer();
        } catch (NotFound|LockingFailure $exception) {
            $this->logDebug(static::LOG_MSG_SKIPPED, ['period' => $migrationPeriod, $exception->getMessage()], [static::LOG_CODE, static::LOG_CODE_SKIPPED]);
            $output->write(sprintf(static::LOG_MSG_SKIPPED, $migrationPeriod, sprintf('<error>%s</error>', $exception->getMessage())));
            if (isset($lock)) {
                $this->lockingService->unlock($lock);
            }
            return;
        }

        $this->logDebug(static::LOG_MSG_FOUND_DATA, [number_format($collection->count()), $collection->count() != 1 ? 's' : '', 'period' => $migrationPeriod], [static::LOG_CODE, static::LOG_CODE_FOUND_DATA]);
        $output->write(sprintf(static::LOG_MSG_FOUND_DATA . '... ', number_format($collection->count()), $collection->count() != 1 ? 's' : '', $migrationPeriod));

        try {
            $this->storage->beginTransaction();
            $this->archive->saveCollection($collection, $migrationTimer);
            $this->storage->removeCollectionForMigrationPeriod($migrationPeriod);
            $this->storage->commitTransaction();
        } catch (\Throwable $throwable) {
            $this->storage->rollbackTransaction();
            $this->logAlertException($throwable, static::LOG_MSG_FAILURE, [], [static::LOG_CODE, static::LOG_CODE_FAILURE], ['period' => $migrationPeriod]);
            $output->writeln(sprintf('<error>%s</error>', static::LOG_MSG_FAILURE));
            return;
        } finally {
            $totalTimer();
            $this->logDebug(static::LOG_MSG_MIGRATION_TIMINGS, ['timings.total' => $migrationTimer->getTotal()], [static::LOG_CODE, static::LOG_CODE_MIGRATION_TIMINGS], ['period' => $migrationPeriod, 'timings.load' => $migrationTimer->getLoad(), 'timings.compression' => $migrationTimer->getCompression(), 'timings.upload' => $migrationTimer->getUpload()]);
            $this->lockingService->unlock($lock);
        }

        $this->logDebug(static::LOG_MSG_MIGRATED, [], [static::LOG_CODE, static::LOG_CODE_MIGRATED], ['period' => $migrationPeriod]);
        $output->writeln(sprintf('<info>%s</info>', static::LOG_MSG_MIGRATED));
    }
}