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
    protected const LOG_MSG_TIME_FRAME = 'Migrating stock audit adjustments older than %s (%s) - limited to %d';
    protected const LOG_CODE_UNSUPPORTED_STORAGE = 'Can not migrate from storage';
    protected const LOG_MSG_UNSUPPORTED_STORAGE = 'Can not migrate from storage';
    protected const LOG_CODE_NO_DATA = 'Found no stock audit adjustments to migrate';
    protected const LOG_MSG_NO_DATA = 'Found no stock audit adjustments to migrate';
    protected const LOG_CODE_FOUND_DATA = 'Found stock audit adjustments to migrate';
    protected const LOG_MSG_FOUND_DATA = 'Found %d stock audit adjustment%s to migrate';
    protected const LOG_CODE_FAILURE = 'Failed to migrate stock audit adjustments';
    protected const LOG_MSG_FAILURE = 'Failed to migrate stock audit adjustments';
    protected const LOG_CODE_MIGRATED = 'Migrated stock audit adjustments';
    protected const LOG_MSG_MIGRATED = 'Migrated stock audit adjustments';

    /** @var StorageInterface */
    protected $storage;
    /** @var StorageInterface */
    protected $archive;

    public function __construct(StorageInterface $storage, StorageInterface $archive)
    {
        $this->storage = $storage;
        $this->archive = $archive;
    }

    public function __invoke(OutputInterface $output, string $timeFrame, int $limit)
    {
        $date = new Date((new DateTime($timeFrame))->resetTime()->stdDateFormat());
        $this->logDebug(static::LOG_MSG_TIME_FRAME, [$timeFrame, 'date' => $date->getDate(), 'limit' => $limit], [static::LOG_CODE, static::LOG_CODE_TIME_FRAME]);
        $output->writeln(sprintf(static::LOG_MSG_TIME_FRAME, $timeFrame, $date->getDate(), $limit));

        if (!($this->storage instanceof MigrationInterface)) {
            $exception = new \LogicException(sprintf('Storage does not implement %s', MigrationInterface::class));
            $this->logErrorException($exception, static::LOG_MSG_UNSUPPORTED_STORAGE, [], [static::LOG_CODE, static::LOG_CODE_UNSUPPORTED_STORAGE]);
            $output->writeln(sprintf('<error>%s</error>', static::LOG_MSG_UNSUPPORTED_STORAGE));
            throw $exception;
        }

        try {
            $collection = $this->storage->fetchCollectionOlderThan($date, $limit);
        } catch (NotFound $exception) {
            $this->logWarningException($exception, static::LOG_MSG_NO_DATA, [], [static::LOG_CODE, static::LOG_CODE_NO_DATA]);
            $output->writeln(sprintf('<error>%s</error>', static::LOG_MSG_NO_DATA));
            return;
        }

        $this->logDebug(static::LOG_MSG_FOUND_DATA, [$collection->count(), $collection->count() != 1 ? 's' : ''], [static::LOG_CODE, static::LOG_CODE_FOUND_DATA]);
        $output->writeln(sprintf(static::LOG_MSG_FOUND_DATA, $collection->count(), $collection->count() != 1 ? 's' : ''));

        $this->storage->beginTransaction();
        try {
            $this->archive->saveCollection($collection);
            $this->storage->removeCollection($collection);
            $this->storage->commitTransaction();
        } catch (\Throwable $throwable) {
            $this->storage->rollbackTransaction();
            $this->logAlertException($throwable, static::LOG_MSG_FAILURE, [], [static::LOG_CODE, static::LOG_CODE_FAILURE]);
            $output->writeln(sprintf('<error>%s</error>', static::LOG_MSG_FAILURE));
            throw $throwable;
        }

        $this->logDebug(static::LOG_MSG_MIGRATED, [], [static::LOG_CODE, static::LOG_CODE_MIGRATED]);
        $output->writeln(sprintf('<info>%s</info>', static::LOG_MSG_MIGRATED));
    }
}