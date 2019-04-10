<?php
namespace CG\Stock\Audit\Combined\Storage;

use CG\Stdlib\DateTime;
use CG\Stock\Audit\Adjustment\Storage\FileStorage as AuditAdjustmentFileStorage;
use CG\Stock\Audit\Adjustment\StorageInterface as AuditAdjustmentStorage;
use CG\Stock\Audit\Combined\Filter;
use CG\Stock\Audit\Combined\StorageInterface;
use CG\Stock\Audit\Combined\Type;

class FileStorage implements StorageInterface
{
    /** @var StorageInterface */
    protected $storage;
    /** @var AuditAdjustmentStorage */
    protected $auditAdjustmentStorage;
    /** @var AuditAdjustmentFileStorage */
    protected $auditAdjustmentFileStorage;

    public function __construct(
        StorageInterface $storage,
        AuditAdjustmentStorage $auditAdjustmentStorage,
        AuditAdjustmentFileStorage $auditAdjustmentFileStorage
    ) {
        $this->storage = $storage;
        $this->auditAdjustmentStorage = $auditAdjustmentStorage;
        $this->auditAdjustmentFileStorage = $auditAdjustmentFileStorage;
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        $this->reloadAuditAdjustmentData($filter);
        return $this->storage->fetchCollectionByFilter($filter);
    }

    protected function reloadAuditAdjustmentData(Filter $filter): void
    {
        $ouIds = $filter->getOrganisationUnitId();
        $skus = $filter->getSku();
        $dateFrom = $filter->getDateTimeFrom();
        $dateTo = $filter->getDateTimeTo();
        $type = $filter->getType();

        if (
            empty($ouIds)
            || empty($skus)
            || empty($dateFrom)
            || empty($dateTo)
            || (!empty($type) && !in_array(Type::ADJUSTMENT, $type))
        ) {
            return;
        }

        $this->auditAdjustmentStorage->saveCollection(
            $this->auditAdjustmentFileStorage->fetchCollection(
                $ouIds,
                $skus,
                new DateTime($dateFrom),
                new DateTime($dateTo)
            )
        );
    }
}