<?php
namespace CG\Stock\Audit\Adjustment\Storage;

use CG\FileStorage\AdapterInterface as StorageAdapter;
use CG\Stdlib\CollectionInterface;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stock\Audit\Adjustment\Collection as AuditAdjustments;
use CG\Stock\Audit\Adjustment\Entity as AuditAdjustment;
use CG\Stock\Audit\Adjustment\Storage\FileStorage\Cache;
use CG\Stock\Audit\Adjustment\Storage\FileStorage\File;
use CG\Stock\Audit\Adjustment\Storage\FileStorage\Mapper;
use CG\Stock\Audit\Adjustment\StorageInterface;

class FileStorage implements StorageInterface, LoggerAwareInterface
{
    use LogTrait;

    protected const LOG_CODE = 'StockAuditAdjustment::FileStorage';
    protected const LOG_CODE_EXPENSIVE_METHOD_CALL = 'ExpensiveMethodCall';
    protected const LOG_MSG_EXPENSIVE_METHOD_CALL = '%s is expensive as it requires loading an entire days file for one entity - this should method should be avoided where possible';

    /** @var StorageAdapter */
    protected $storageAdapter;
    /** @var Mapper */
    protected $mapper;
    /** @var Cache */
    protected $cache;

    public function __construct(StorageAdapter $storageAdapter, Mapper $mapper, Cache $cache)
    {
        $this->storageAdapter = $storageAdapter;
        $this->mapper = $mapper;
        $this->cache = $cache;
    }

    /**
     * @param AuditAdjustment $entity
     */
    public function save($entity)
    {
        $this->logWarning(static::LOG_MSG_EXPENSIVE_METHOD_CALL, [__METHOD__], [static::LOG_CODE, static::LOG_CODE_EXPENSIVE_METHOD_CALL]);
        $filename = $this->generateEntityFilename($entity);
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
        $this->logWarning(static::LOG_MSG_EXPENSIVE_METHOD_CALL, [__METHOD__], [static::LOG_CODE, static::LOG_CODE_EXPENSIVE_METHOD_CALL]);
        $filename = $this->generateEntityFilename($entity);
        $file = $this->loadFile($filename);
        if (!isset($file[$entity->getId()])) {
            return;
        }
        unset($file[$entity->getId()]);
        $this->saveFile($filename, $file);
    }

    public function fetchCollection(array $ouIds, DateTime $from, DateTime $to): AuditAdjustments
    {
        $collection = new AuditAdjustments();
        for ($date = $from->resetTime(); $date <= $to->resetTime(); $date->addOneDay()) {
            foreach ($ouIds as $ouId) {
                $filename = $this->generateFilename($ouId, $date->stdDateFormat());
                foreach ($this->loadFile($filename) as $entity) {
                    $collection->attach($entity);
                }
            }
        }
        return $collection;
    }

    public function saveCollection(CollectionInterface $collection)
    {
        /** @var AuditAdjustment[] $files */
        $files = [];

        /** @var AuditAdjustment $entity */
        foreach ($collection as $entity) {
            $filename = $this->generateEntityFilename($entity);
            $files[$filename] = $files[$filename] ?? [];
            $files[$filename][] = $entity;
        }

        foreach ($files as $filename => $entities) {
            $file = $this->loadFile($filename, true);
            foreach ($entities as $entity) {
                $file[$entity->getId()] = $entity;
            }
            $this->saveFile($filename, $file);
        }

        return $collection;
    }

    protected function generateEntityFilename(AuditAdjustment $entity): string
    {
        return $this->generateFilename($entity->getOrganisationUnitId(), $entity->getDate());
    }

    protected function generateFilename(int $outId, string $date): string
    {
        return ENVIRONMENT . '/AuditAdjustment/' . sprintf('%d-%s.json', $outId, $date);
    }

    protected function loadFile(string $filename, bool $useCache = false): File
    {
        return $this->mapper->toFile(
            $this->loadFileData($filename, $useCache)
        );
    }

    protected function loadFileData(string $filename, bool $useCache): ?string
    {
        try {
            if ($useCache) {
                return $this->cache->loadFile($filename);
            }
        } catch (NotFound $exception) {
            // Ignore - try real storage
        }

        try {
            $data = $this->storageAdapter->read($filename)->getBody();
            $this->cache->saveFile($filename, $data);
            return $data;
        } catch (NotFound $exception) {
            return null;
        }
    }

    protected function saveFile(string $filename, File $file): void
    {
        if ($file->isModified()) {
            $this->cache->removeFile($filename);
            $this->storageAdapter->write($filename, $this->mapper->fromFile($file));
        }
    }
}