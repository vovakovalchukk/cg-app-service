<?php
namespace CG\Stock\Audit\Adjustment\Storage;

use CG\FileStorage\AdapterInterface as StorageAdapter;
use CG\Stdlib\CollectionInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Audit\Adjustment\Entity as AuditAdjustment;
use CG\Stock\Audit\Adjustment\Storage\FileStorage\File;
use CG\Stock\Audit\Adjustment\Storage\FileStorage\Mapper;
use CG\Stock\Audit\Adjustment\StorageInterface;

class FileStorage implements StorageInterface
{
    /** @var StorageAdapter */
    protected $storageAdapter;
    /** @var Mapper */
    protected $mapper;

    public function __construct(StorageAdapter $storageAdapter, Mapper $mapper)
    {
        $this->storageAdapter = $storageAdapter;
        $this->mapper = $mapper;
    }

    /**
     * @param AuditAdjustment $entity
     */
    public function save($entity)
    {
        $filename = $this->generateFilename($entity);
        $file = $this->loadFile($filename);
        $file[$entity->getId()] = $entity;
        $this->saveFile($filename, $file);
        return $entity;
    }

    /**
     * @param AuditAdjustment $entity
     */
    public function remove($entity)
    {
        $filename = $this->generateFilename($entity);
        $file = $this->loadFile($filename);
        if (!isset($file[$entity->getId()])) {
            return;
        }
        unset($file[$entity->getId()]);
        $this->saveFile($filename, $file);
    }

    public function saveCollection(CollectionInterface $collection)
    {
        /** @var AuditAdjustment[] $files */
        $files = [];

        /** @var AuditAdjustment $entity */
        foreach ($collection as $entity) {
            $filename = $this->generateFilename($entity);
            $files[$filename] = $files[$filename] ?? [];
            $files[$filename][] = $entity;
        }

        foreach ($files as $filename => $entities) {
            $file = $this->loadFile($filename);
            foreach ($entities as $entity) {
                $file[$entity->getId()] = $entity;
            }
            $this->saveFile($filename, $file);
        }

        return $collection;
    }

    protected function generateFilename(AuditAdjustment $entity): string
    {
        return sprintf('Audit-Adjustment-%d-%s.json', $entity->getOrganisationUnitId(), $entity->getDate());
    }

    protected function loadFile(string $filename): File
    {
        try {
            $data = $this->storageAdapter->read($filename);
        } catch (NotFound $exception) {
            $data = null;
        }
        return $this->mapper->toFile($data);
    }

    protected function saveFile(string $filename, File $file): void
    {
        $this->storageAdapter->write($filename, $this->mapper->fromFile($file));
    }
}