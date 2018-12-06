<?php
namespace CG\Stock\Audit\Adjustment\Storage\FileStorage;

use CG\Stock\Audit\Adjustment\Entity as AuditAdjustment;
use CG\Stock\Audit\Adjustment\Mapper as AuditAdjustmentMapper;

class Mapper
{
    /** @var AuditAdjustmentMapper */
    protected $auditAdjustmentMapper;

    public function __construct(AuditAdjustmentMapper $auditAdjustmentMapper)
    {
        $this->auditAdjustmentMapper = $auditAdjustmentMapper;
    }

    public function toFile(?string $data): File
    {
        $file = new File();
        if ($data === null) {
            return $file;
        }

        $collection = json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(json_last_error_msg(), json_last_error());
        }

        foreach ($collection as $entityArray) {
            /** @var AuditAdjustment $entity */
            $entity = $this->auditAdjustmentMapper->fromArray($entityArray);
            $file[$entity->getId()] = $entity;
        }

        return $file;
    }

    public function fromFile(?File $file): string
    {
        return json_encode($this->toArray($file));
    }

    protected function toArray(?File $file): array
    {
        if ($file === null) {
            return [];
        }

        $collection = [];
        /** @var AuditAdjustment $entity */
        foreach ($file as $entity) {
            $collection[] = $entity->toArray();
        }
        return $collection;
    }
}