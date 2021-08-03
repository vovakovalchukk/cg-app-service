<?php
namespace CG\Stock\Command;

use CG\CGLib\Command\SignalHandlerTrait;
use CG\Stdlib\Date;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stock\Audit\Adjustment\AbortException;
use CG\Stock\Audit\Adjustment\Collection as Adjustments;
use CG\Stock\Audit\Adjustment\Entity as Adjustment;
use CG\Stock\Audit\Adjustment\RawDataCollection as AdjustmentsRawData;
use CG\Stock\Audit\Adjustment\Mapper as AdjustmentMapper;
use CG\Stock\Audit\Adjustment\MigrationInterface;
use CG\Stock\Audit\Adjustment\Related\Collection as AdjustmentRelateds;
use CG\Stock\Audit\Adjustment\Related\RawDataCollection as AdjustmentRelatedsRawData;
use CG\Stock\Audit\Adjustment\Related\Mapper as AdjustmentRelatedMapper;
use CG\Stock\Audit\Adjustment\StorageInterface as AdjustmentStorage;
use CG\Stock\Audit\Adjustment\Related\StorageInterface as AdjustmentRelatedStorage;
use CG\Stock\Locking\Audit\Adjustment\MigrationPeriod;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertArchivedStockAuditAdjustments implements LoggerAwareInterface
{
    use LogTrait;
    use SignalHandlerTrait;

    protected const LOG_CODE = 'ConvertArchivedStockAuditAdjustments';
    protected const LOG_CODE_TIME_FRAME = 'Converting stock audit adjustments';
    protected const LOG_MSG_TIME_FRAME = 'Converting stock audit adjustments older than or equal to %s';
    protected const LOG_CODE_UNSUPPORTED_STORAGE = 'Can not fetch migration periods from storage';
    protected const LOG_MSG_UNSUPPORTED_STORAGE = 'Can not fetch migration periods from storage';
    protected const LOG_CODE_NO_DATA = 'Found no stock audit adjustments to convert';
    protected const LOG_MSG_NO_DATA = 'Found no stock audit adjustments to convert';
    protected const LOG_CODE_NO_DATA_FOR_PERIOD = 'Found no stock audit adjustments to convert for period %s';
    protected const LOG_MSG_NO_DATA_FOR_PERIOD = 'Found no stock audit adjustments to convert for period %s';
    protected const LOG_CODE_CONVERSION_EXCEPTION = 'Exception thrown whilst converting data';
    protected const LOG_MSG_CONVERSION_EXCEPTION = 'Exception of type %s thrown whilst converting data';
    protected const LOG_CODE_CONVERSION_SUCCESSFUL = 'Successfully converted all data';
    protected const LOG_MSG_CONVERSION_SUCCESSFUL = 'Successfully converted all data for period %s';
    protected const LOG_CODE_TRANSACTION_ROLLBACK = 'Rolling back transaction';
    protected const LOG_MSG_TRANSACTION_ROLLBACK_ADJUSTMENT = 'Rolling back transaction where adjustments are removed';
    protected const LOG_MSG_TRANSACTION_ROLLBACK_RELATED = 'Rolling back transaction where adjustment relateds are saved';

    /** @var AdjustmentStorage | MigrationInterface */
    protected $adjustmentStorage;
    /** @var AdjustmentRelatedStorage */
    protected $adjustmentRelatedStorage;
    /** @var AdjustmentMapper */
    protected $adjustmentMapper;
    /** @var AdjustmentRelatedMapper */
    protected $adjustmentRelatedMapper;

    public function __construct(
        AdjustmentStorage $adjustmentStorage,
        AdjustmentRelatedStorage $adjustmentRelatedStorage,
        AdjustmentMapper $adjustmentMapper,
        AdjustmentRelatedMapper $adjustmentRelatedMapper
    ) {
        $this->adjustmentStorage = $adjustmentStorage;
        $this->adjustmentRelatedStorage = $adjustmentRelatedStorage;
        $this->adjustmentMapper = $adjustmentMapper;
        $this->adjustmentRelatedMapper = $adjustmentRelatedMapper;
    }

    public function __invoke(OutputInterface $output, ?string $timeFrame): void
    {
        try {
            $this->registerSignalHandler(function (int $signal) {
                throw new AbortException(sprintf('Aborting due to %s signal', $this->signalName($signal)));
            }, SIGINT, SIGTERM);
            $this->convertData($output, $timeFrame);
        } finally {
            $this->restoreSignalHandler(SIGINT, SIGTERM);
        }
    }

    protected function convertData(OutputInterface $output, ?string $timeFrame): void
    {
        $date = new Date((new DateTime($timeFrame))->resetTime()->stdDateFormat());
        $this->logDebug(static::LOG_MSG_TIME_FRAME, ['date' => $date->getDate()], [static::LOG_CODE, static::LOG_CODE_TIME_FRAME]);
        $output->writeln(sprintf(static::LOG_MSG_TIME_FRAME, $date->getDate()));

        if (!($this->adjustmentStorage instanceof MigrationInterface)) {
            $exception = new \LogicException(sprintf('Storage does not implement %s', MigrationInterface::class));
            $this->logErrorException($exception, static::LOG_MSG_UNSUPPORTED_STORAGE, [], [static::LOG_CODE, static::LOG_CODE_UNSUPPORTED_STORAGE]);
            $output->writeln(sprintf('<error>%s</error>', static::LOG_MSG_UNSUPPORTED_STORAGE));
            throw $exception;
        }

        $migrationPeriods = $this->adjustmentStorage->fetchMigrationPeriodsWithDataOlderThanOrEqualTo($date);
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
                    $this->convertByMigrationPeriod($output, new MigrationPeriod($migrationPeriod));
                } finally {
                    gc_collect_cycles();
                    gc_mem_caches();
                }
            }
        } finally {
            gc_enable();
        }
    }

    protected function convertByMigrationPeriod(OutputInterface $output, MigrationPeriod $migrationPeriod): void
    {
        $this->beginTransactions();
        $adjustmentRelatedsCreatedAndSaved = false;
        $relevantAdjustmentsRemoved = false;
        // the command doesn't take an optional limit due to the nature of how it works
        // it needs to grab everything in one go. we will revisit if/when necessary
        try {
            $adjustments = $this->filterToRelevantAdjustments(
                $this->fetchAdjustments($migrationPeriod)
            );
            $adjustmentRelatedsCreatedAndSaved = $this->saveAdjustmentRelateds(
                $this->createAdjustmentRelateds($adjustments)
            );
            $relevantAdjustmentsRemoved = $this->removeAdjustments($adjustments);
        } catch (NotFound $e) {
            $this->logWarning(static::LOG_MSG_NO_DATA_FOR_PERIOD, [(string)$migrationPeriod], [static::LOG_CODE, static::LOG_CODE_NO_DATA_FOR_PERIOD]);
            $output->writeln(sprintf('<error>%s</error>', sprintf(static::LOG_MSG_NO_DATA_FOR_PERIOD, (string)$migrationPeriod)));
            return;
        } catch (\Throwable $e) {
            $this->logDebugException($e, static::LOG_MSG_CONVERSION_EXCEPTION, [get_class($e)], [static::LOG_CODE, static::LOG_CODE_CONVERSION_EXCEPTION]);
            $output->writeln(sprintf('<error>%s</error>', sprintf(static::LOG_MSG_CONVERSION_EXCEPTION, get_class($e))));
            return;
        } finally {
            $this->concludeTransactions($output, $migrationPeriod, $adjustmentRelatedsCreatedAndSaved, $relevantAdjustmentsRemoved);
        }
    }

    protected function fetchAdjustments(MigrationPeriod $migrationPeriod): Adjustments
    {
        return $this->adjustmentStorage->fetchCollectionForMigrationPeriod($migrationPeriod);
    }

    protected function filterToRelevantAdjustments(Adjustments $adjustments): AdjustmentsRawData
    {
        return new AdjustmentsRawData(
            $this->adjustmentMapper,
            array_filter(
                $this->getDataForAdjustments($adjustments),
                function (array $adjustmentDatum) {
                    return ($adjustmentDatum['referenceSku'] ?? null) && is_int(($adjustmentDatum['referenceQuantity'] ?? null));
                }
            )
        );
    }

    protected function getDataForAdjustments(Adjustments $adjustments): array
    {
        if (!($adjustments instanceof AdjustmentsRawData) || !$adjustments->hasRawData()) {
            return array_map(
                function (Adjustment $adjustment) {
                    return $adjustment->toArray();
                },
                iterator_to_array($adjustments)
            );
        }
        return is_array($adjustments->getRawData()) ? $adjustments->getRawData() : iterator_to_array($adjustments->getRawData());
    }

    protected function createAdjustmentRelateds(Adjustments $adjustments): AdjustmentRelateds
    {
        return new AdjustmentRelatedsRawData(
            $this->adjustmentRelatedMapper,
            array_map(
                function (array $adjustmentDatum) {
                    return [
                        'id' => $adjustmentDatum['id'],
                        'stockAdjustmentLogId' => substr($adjustmentDatum['id'], 0, 0 - (strlen($adjustmentDatum['sku']) + 1)),
                        'organisationUnitId' => $adjustmentDatum['organisationUnitId'],
                        'sku' => $adjustmentDatum['sku'],
                        'quantity' => $adjustmentDatum['quantity'],
                    ];
                },
                $this->getDataForAdjustments($adjustments)
            )
        );
    }

    protected function saveAdjustmentRelateds(AdjustmentRelateds $adjustmentRelateds): bool
    {
        if ($adjustmentRelateds->count() == 0) {
            return true;
        }
        try {
            $this->adjustmentRelatedStorage->saveCollection($adjustmentRelateds);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function removeAdjustments(Adjustments $adjustments): bool
    {
        if ($adjustments->count() == 0) {
            return true;
        }
        try {
            $this->adjustmentStorage->removeCollection($adjustments);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function beginTransactions(): void
    {
        $this->adjustmentStorage->beginTransaction();
        $this->adjustmentRelatedStorage->beginTransaction();
    }

    protected function concludeTransactions(
        OutputInterface $output,
        MigrationPeriod $migrationPeriod,
        bool $adjustmentRelatedsCreatedAndSaved,
        bool $relevantAdjustmentsRemoved
    ): void
    {
        if ($adjustmentRelatedsCreatedAndSaved && $relevantAdjustmentsRemoved) {
            $this->adjustmentStorage->commitTransaction();
            $this->adjustmentRelatedStorage->commitTransaction();
            $this->logDebug(static::LOG_MSG_CONVERSION_SUCCESSFUL, [(string) $migrationPeriod], [static::LOG_CODE, static::LOG_CODE_CONVERSION_SUCCESSFUL]);
            $output->writeln(sprintf(static::LOG_MSG_CONVERSION_SUCCESSFUL, (string) $migrationPeriod));
            return;
        }
        if (!$adjustmentRelatedsCreatedAndSaved) {
            $this->logDebug(static::LOG_MSG_TRANSACTION_ROLLBACK_RELATED, [], [static::LOG_CODE, static::LOG_CODE_TRANSACTION_ROLLBACK]);
            $this->adjustmentRelatedStorage->rollbackTransaction();
        }
        if (!$relevantAdjustmentsRemoved) {
            $this->logDebug(static::LOG_MSG_TRANSACTION_ROLLBACK_ADJUSTMENT, [], [static::LOG_CODE, static::LOG_CODE_TRANSACTION_ROLLBACK]);
            $this->adjustmentStorage->rollbackTransaction();
        }
    }
}