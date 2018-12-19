<?php
namespace CG\Stock\Audit\Adjustment\Storage;

use CG\FileStorage\AsyncAdapterInterface as StorageAdapter;
use CG\FileStorage\Promise;
use CG\FileStorage\PromiseInterface;
use CG\FileStorage\ResponseInterface;
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

    public function fetchCollection(array $ouIds, array $skus, DateTime $from, DateTime $to): AuditAdjustments
    {
        /** @var PromiseInterface[] $promises */
        $promises = [];
        for ($date = $from->resetTime(); $date <= $to->resetTime(); $date->addOneDay()) {
            foreach ($ouIds as $ouId) {
                foreach ($skus as $sku) {
                    $filename = $this->generateFilename($ouId, $date->stdDateFormat(), $sku);
                    $promises[] = $this->loadFileAsync($filename);
                }
            }
        }

        $collection = new AuditAdjustments();
        foreach ($promises as $promise) {
            /** @var File $file */
            $file = $promise->wait();
            foreach ($file as $entity) {
                $collection->attach($entity);
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

        $promises = [];
        foreach ($files as $filename => $entities) {
            $file = $this->loadFile($filename, true);
            foreach ($entities as $entity) {
                $file[$entity->getId()] = $entity;
            }
            $promises[] = $this->saveFileAsync($filename, $file);
        }

        foreach ($promises as $promise) {
            if ($promise instanceof PromiseInterface) {
                $promise->wait();
            }
        }

        return $collection;
    }

    protected function generateEntityFilename(AuditAdjustment $entity): string
    {
        return $this->generateFilename($entity->getOrganisationUnitId(), $entity->getDate(), $entity->getSku());
    }

    protected function generateFilename(int $outId, string $date, string $sku): string
    {
        return ENVIRONMENT . '/AuditAdjustment/' . sprintf('%d-%s-%s.json', $outId, $date, base64_encode($sku));
    }

    protected function loadFile(string $filename, bool $useCache = false): File
    {
        return $this->loadFileAsync($filename, $useCache)->wait();
    }

    protected function loadFileAsync(string $filename, bool $useCache = false): PromiseInterface
    {
        return $this->loadFileData($filename, $useCache)->then(function(?string $data) {
            return $this->mapper->toFile($data);
        });
    }

    protected function loadFileData(string $filename, bool $useCache): PromiseInterface
    {
        try {
            if ($useCache) {
                return Promise::createResolved(
                    $this->cache->loadFile($filename)
                );
            }
        } catch (NotFound $exception) {
            // Ignore - try real storage
        }

        $promise = $this->storageAdapter->readAsync($filename);
        $promise->then(
            function(ResponseInterface $response) use($filename) {
                $data = $response->getBody();
                $this->cache->saveFile($filename, $data);
                return $data;
            },
            function(\Throwable $throwable) {
                if ($throwable instanceof NotFound) {
                    return null;
                }
                throw $throwable;
            }
        );
        return $promise;
    }

    protected function saveFile(string $filename, File $file): void
    {
        $promise = $this->saveFileAsync($filename, $file);
        if ($promise instanceof PromiseInterface) {
            $promise->wait();
        }
    }

    protected function saveFileAsync(string $filename, File $file): ?PromiseInterface
    {
        if (!$file->isModified()) {
            return null;
        }

        $this->cache->removeFile($filename);
        return $this->storageAdapter->writeAsync($filename, $this->mapper->fromFile($file));
    }
}