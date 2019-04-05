<?php
namespace CG\Stock\Audit\Adjustment\Storage;

use CG\FileStorage\AsyncAdapterInterface as StorageAdapter;
use CG\FileStorage\Promise;
use CG\FileStorage\PromiseInterface;
use CG\FileStorage\ResponseInterface;
use CG\Stdlib\CollectionInterface;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stock\Audit\Adjustment\Collection as AuditAdjustments;
use CG\Stock\Audit\Adjustment\Entity as AuditAdjustment;
use CG\Stock\Audit\Adjustment\MigrationTimer;
use CG\Stock\Audit\Adjustment\Storage\FileStorage\Cache;
use CG\Stock\Audit\Adjustment\Storage\FileStorage\File;
use CG\Stock\Audit\Adjustment\Storage\FileStorage\Mapper;
use CG\Stock\Audit\Adjustment\StorageInterface;

class FileStorage implements StorageInterface, LoggerAwareInterface
{
    use LogTrait;

    protected const LOG_CODE = 'StockAuditAdjustment::FileStorage';
    protected const LOG_CODE_EXPENSIVE_METHOD_CALL = 'ExpensiveMethodCall';
    protected const LOG_MSG_EXPENSIVE_METHOD_CALL = '%s is expensive as it requires loading an entire days file for one entity - this method should be avoided where possible';
    protected const LOG_CODE_CONFLICT = 'Did not save file as modified';
    protected const LOG_MSG_CONFLICT = 'Did not save file as modified';

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
        try {
            $this->saveFile($file);
        } catch (Conflict $conflict) {
            $this->logWarningException($conflict, static::LOG_MSG_CONFLICT, [], static::LOG_CODE_CONFLICT);
        }
        return $entity;
    }

    /**
     * @param AuditAdjustment $entity
     */
    public function remove($entity)
    {
        $this->logWarning(static::LOG_MSG_EXPENSIVE_METHOD_CALL, [__METHOD__], [static::LOG_CODE, static::LOG_CODE_EXPENSIVE_METHOD_CALL]);

        /** @var PromiseInterface[] $promises */
        $promises = [];
        foreach ([$this->generateEntityFilename($entity), $this->generateLegacyEntityFilename($entity)] as $filename) {
            $promises[] = $this->loadFileAsync($filename);
        }

        foreach ($promises as $promise) {
            /** @var File $file */
            $file = $promise->wait();
            if (!isset($file[$entity->getId()])) {
                continue;
            }

            unset($file[$entity->getId()]);
            try {
                $this->saveFile($file);
            } catch (Conflict $conflict) {
                $this->logWarningException($conflict, static::LOG_MSG_CONFLICT, [], static::LOG_CODE_CONFLICT);
            }
        }
    }

    public function fetchCollection(array $ouIds, array $skus, DateTime $from, DateTime $to): AuditAdjustments
    {
        /** @var PromiseInterface[] $promises */
        $promises = [];
        for ($date = $from->resetTime(); $date <= $to->resetTime(); $date->addOneDay()) {
            foreach ($ouIds as $ouId) {
                foreach ($skus as $sku) {
                    $filename = $this->generateFilename($ouId, $date, $sku);
                    if (!isset($promises[$filename])) {
                        $promises[$filename] = $this->loadFileAsync($filename);
                    }

                    $legacyFilename = $this->generateLegacyFilename($ouId, $date, $sku);
                    if (!isset($promises[$legacyFilename])) {
                        $promises[$legacyFilename] = $this->loadFileAsync($legacyFilename);
                    }
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

    public function saveCollection(CollectionInterface $collection, MigrationTimer $migrationTimer = null)
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
            $promises[] = $this->loadFileAsync($filename, true)
                ->then(function(File $file) use($filename, $entities, $migrationTimer) {
                    foreach ($entities as $entity) {
                        $file[$entity->getId()] = $entity;
                    }
                    return $this->saveFileAsync($file, $migrationTimer);
                });
        }

        /** @var PromiseInterface $promise */
        while ($promise = array_pop($promises)) {
            try {
                $response = $promise->wait();
                if ($response instanceof PromiseInterface) {
                    array_push($promises, $response);
                }
            } catch (Conflict $conflict) {
                $this->logWarningException($conflict, static::LOG_MSG_CONFLICT, [], static::LOG_CODE_CONFLICT);
            }
        }

        return $collection;
    }

    protected function generateEntityFilename(AuditAdjustment $entity): string
    {
        return $this->generateFilename($entity->getOrganisationUnitId(), new DateTime($entity->getDate()), $entity->getSku());
    }

    protected function generateLegacyEntityFilename(AuditAdjustment $entity): string
    {
        return $this->generateLegacyFilename($entity->getOrganisationUnitId(), new DateTime($entity->getDate()), $entity->getSku());
    }

    protected function generateFilename(int $ouId, DateTime $date, string $sku): string
    {
        return ENVIRONMENT . '/AuditAdjustment/' . sprintf(
            '%s/%s/%d/%s.json.gz',
            $date->format('o'),
            $date->format('W'),
            $ouId,
            base64_encode($sku)
        );
    }

    protected function generateLegacyFilename(int $ouId, DateTime $date, string $sku): string
    {
        return ENVIRONMENT . '/AuditAdjustment/' . sprintf(
            '%d-%s-%s.json',
            $ouId,
            $date->stdDateFormat(),
            base64_encode($sku)
        );
    }

    protected function loadFile(string $filename, bool $useCache = false): File
    {
        return $this->loadFileAsync($filename, $useCache)->wait();
    }

    protected function loadFileAsync(string $filename, bool $useCache = false): PromiseInterface
    {
        $compressed = substr($filename, -8) == '.json.gz';
        return $this->loadFileData($filename, $useCache)->then(function(?string $data) use($filename, $compressed) {
            return $this->mapper->toFile($filename, $data, $compressed);
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

    protected function saveFile(File $file, MigrationTimer $migrationTimer = null): void
    {
        $promise = $this->saveFileAsync($file, $migrationTimer);
        if ($promise instanceof PromiseInterface) {
            $promise->wait();
        }
    }

    protected function saveFileAsync(File $file, MigrationTimer $migrationTimer = null): ?PromiseInterface
    {
        $modified = $file->isModified();
        if ($file->count() == 0 || !$modified) {
            return null;
        }
        if ($file->getInitialCount() > 0 && $modified) {
            return Promise::createRejected(new Conflict(
                sprintf('Failed to save %s, hash does not match (%s != %s)', $file->getFilename(), $file->getHash(), $file->hash())
            ));
        }

        $this->cache->removeFile($file->getFilename());
        $promise = $this->storageAdapter->writeAsync($file->getFilename(), $this->mapper->fromFile($file, $migrationTimer));

        if ($migrationTimer === null) {
            return $promise;
        }

        $uploadTimer = $migrationTimer->getUploadTimer();
        $promise->then(
            function($response) use($uploadTimer) {
                $uploadTimer();
                return $response;
            },
            function($response) use($uploadTimer) {
                $uploadTimer();
                return $response;
            }
        );

        return $promise;
    }
}