<?php
namespace CG\Stock\Audit\Adjustment\Storage\FileStorage;

use CG\Stock\Audit\Adjustment\Entity as AuditAdjustment;
use CG\Stock\Audit\Adjustment\Mapper as AuditAdjustmentMapper;

class Mapper
{
    protected const COMPRESSION = 9;

    /** @var AuditAdjustmentMapper */
    protected $auditAdjustmentMapper;

    public function __construct(AuditAdjustmentMapper $auditAdjustmentMapper)
    {
        $this->auditAdjustmentMapper = $auditAdjustmentMapper;
    }

    public function toFile(string $filename, ?string $data, bool $compressed): File
    {
        $file = new File($filename, $compressed);
        if ($data === null) {
            return $file;
        }

        if ($compressed) {
            $data = gzdecode($data);
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

        return $file->setInitialCount($file->count())->setHash($file->hash());
    }

    public function fromFile(File $file): string
    {
        $data = json_encode($file->toArray());
        if ($file->isCompressed()) {
            $data = gzencode($data, static::COMPRESSION);
        }
        return $data;
    }
}