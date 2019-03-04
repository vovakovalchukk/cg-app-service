<?php
namespace CG\Stock\Command;

use CG\Stdlib\Date;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stock\Audit\Adjustment\MigrationInterface;
use CG\Stock\Audit\Adjustment\StorageInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateStockAuditAdjustments implements LoggerAwareInterface
{
    use LogTrait;

    protected const LOG_CODE = 'MigrateStockAuditAdjustments';
    protected const LOG_CODE_TIME_FRAME = 'Migrating stock audit adjustments';
    protected const LOG_MSG_TIME_FRAME = 'Migrating stock audit adjustments older than or equal to %s (%s) - limited to %s day%s';
    protected const LOG_CODE_UNSUPPORTED_STORAGE = 'Can not migrate from storage';
    protected const LOG_MSG_UNSUPPORTED_STORAGE = 'Can not migrate from storage';
    protected const LOG_CODE_NO_DATA = 'Found no stock audit adjustments to migrate';
    protected const LOG_MSG_NO_DATA = 'Found no stock audit adjustments to migrate';
    protected const LOG_CODE_FOUND_DATA = 'Found stock audit adjustments to migrate';
    protected const LOG_MSG_FOUND_DATA = 'Found %d stock audit adjustment%s to migrate for %s';
    protected const LOG_CODE_FAILURE = 'Failed to migrate stock audit adjustments';
    protected const LOG_MSG_FAILURE = 'Failed to migrate stock audit adjustments';
    protected const LOG_CODE_MIGRATED = 'Migrated stock audit adjustments';
    protected const LOG_MSG_MIGRATED = 'Migrated stock audit adjustments';

    /** @var StorageInterface|MigrationInterface */
    protected $storage;
    /** @var StorageInterface */
    protected $archive;

    public function __construct(StorageInterface $storage, StorageInterface $archive)
    {
        $this->storage = $storage;
        $this->archive = $archive;
    }

    public function __invoke(OutputInterface $output, string $timeFrame, int $limit = null)
    {
        $date = new Date((new DateTime($timeFrame))->resetTime()->stdDateFormat());
        $this->logDebug(static::LOG_MSG_TIME_FRAME, [$timeFrame, 'date' => $date->getDate(), $limit ?? 'unlimited', $limit != 1 ? 's' : ''], [static::LOG_CODE, static::LOG_CODE_TIME_FRAME]);
        $output->writeln(sprintf(static::LOG_MSG_TIME_FRAME, $timeFrame, $date->getDate(), $limit ?? 'unlimited', $limit != 1 ? 's' : ''));

        if (!($this->storage instanceof MigrationInterface)) {
            $exception = new \LogicException(sprintf('Storage does not implement %s', MigrationInterface::class));
            $this->logErrorException($exception, static::LOG_MSG_UNSUPPORTED_STORAGE, [], [static::LOG_CODE, static::LOG_CODE_UNSUPPORTED_STORAGE]);
            $output->writeln(sprintf('<error>%s</error>', static::LOG_MSG_UNSUPPORTED_STORAGE));
            throw $exception;
        }

        $dates = $this->storage->fetchDatesWithDataOlderThanOrEqualTo($date, $limit);
        if (empty($dates)) {
            $this->logWarning(static::LOG_MSG_NO_DATA, [], [static::LOG_CODE, static::LOG_CODE_NO_DATA]);
            $output->writeln(sprintf('<error>%s</error>', static::LOG_MSG_NO_DATA));
            return;
        }

        foreach ($dates as $date) {
            $this->migrateDate($output, $date);
        }
    }

    protected function migrateDate(OutputInterface $output, Date $date)
    {
        try {
            $collection = $this->storage->fetchCollectionForDate($date);
        } catch (NotFound $exception) {
            // No data for selected date - ignore
            return;
        }

        $this->logDebug(static::LOG_MSG_FOUND_DATA, [$collection->count(), $collection->count() != 1 ? 's' : '', 'date' => $date->getDate()], [static::LOG_CODE, static::LOG_CODE_FOUND_DATA]);
        $output->write(sprintf(static::LOG_MSG_FOUND_DATA . '... ', $collection->count(), $collection->count() != 1 ? 's' : '', $date->getDate()));

        $this->storage->beginTransaction();
        try {
            $this->archive->saveCollection($collection);
            $this->storage->removeCollectionForDate($date);
            $this->storage->commitTransaction();
        } catch (\Throwable $throwable) {
            $this->storage->rollbackTransaction();
            $this->logAlertException($throwable, static::LOG_MSG_FAILURE, [$date->getDate()], [static::LOG_CODE, static::LOG_CODE_FAILURE], ['date' => $date->getDate()]);
            $output->writeln(sprintf('<error>%s</error>', static::LOG_MSG_FAILURE));
            return;
        }

        $this->logDebug(static::LOG_MSG_MIGRATED, [], [static::LOG_CODE, static::LOG_CODE_MIGRATED], ['date' => $date->getDate()]);
        $output->writeln(sprintf('<info>%s</info>', static::LOG_MSG_MIGRATED));
    }
}