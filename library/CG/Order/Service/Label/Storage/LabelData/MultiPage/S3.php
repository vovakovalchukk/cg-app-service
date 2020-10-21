<?php
namespace CG\Order\Service\Label\Storage\LabelData\MultiPage;

use CG\FileStorage\S3\Adapter as S3Adapter;
use CG\Order\Service\Label\Storage\LabelDataInterface;
use CG\Order\Service\Label\Storage\LabelData\S3 as LabelDataS3;
use CG\Predis\Command\HmsetEx;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Predis\Client as PredisClient;
use Predis\PredisException;

class S3 extends LabelDataS3 implements LabelDataInterface
{
    protected const LOG_CODE = 'LabelDataMultiPageS3Storage';

    public function __construct(S3Adapter $s3FileStorage, PredisClient $predisClient)
    {
        parent::__construct($s3FileStorage, $predisClient);
        $this->predisClient->getProfile()->defineCommand('hmsetex', HmsetEx::class);
    }

    public function fetch(int $id, int $ouId): array
    {
        return [
            static::TYPE_DOCUMENT => $this->fetchDocument($id, $ouId),
            static::TYPE_IMAGE    => $this->fetchImages($id, $ouId),
        ];
    }

    protected function fetchImages(int $id, int $ouId): ?array
    {
        $type = static::TYPE_IMAGE;
        $cached = $this->fetchImagesFromCache($id, $ouId, $type);
        if ($cached) {
            return $cached;
        }

        $extension = $this->typeExtensions[$type];
        $images = [];
        $imageIndex = 0;

        try {
            while (true) {
                $result = $this->fileStorage->read($this->getS3KeyImages($ouId, $id, $imageIndex, $extension));
                $images[] = $result->getBody();
                $imageIndex++;
            }
        } catch (NotFound $e) {
            //no-op
        }
        if (!empty($images)) {
            $this->saveImagesToCache($id, $ouId, $images, $type);
            $this->statsIncrement(static::STAT_S3, [$ouId, 'fetch', $type]);
        }

        return $images;
    }

    protected function fetchImagesFromCache(int $id, int $ouId, string $type): ?array
    {
        try {
            $key = $this->getCacheKey($id, $type);
            $data = $this->predisClient->hgetall($key);
            if (!empty($data)) {
                $this->statsIncrement(static::STAT_CACHE, [$ouId, 'fetch', $type]);
            }
            return $data;
        } catch (PredisException $exception) {
            return null;
        }
    }

    public function remove($entity)
    {
        $this->logDebug('About to remove orderLabelDocument entity data in %s', [__METHOD__], static::LOG_CODE);
        $this->removeDocument($entity->getId(), $entity->getOrganisationUnitId());
        $this->removeImages($entity->getId(), $entity->getOrganisationUnitId());
        $this->logDebug('Completed removing orderLabelDocument entity data in %s', [__METHOD__], static::LOG_CODE);
        return $this;
    }

    protected function removeImages(int $id, int $ouId): S3
    {
        $type = static::TYPE_IMAGE;
        try {
            $this->fileStorage->delete($this->getS3KeyImages($ouId, $id));
            $this->statsIncrement(static::STAT_S3, [$ouId, 'remove', $type]);
        } catch (NotFound $e) {
            // No-op
        }
        $this->removeHash($id, $type);
        $this->removeFromCache($id, $ouId, $type);
        return $this;
    }

    public function save($entity)
    {
        $this->saveDocument($entity->getId(), $entity->getOrganisationUnitId(), $entity->getLabel());
        $this->saveImages($entity->getId(), $entity->getOrganisationUnitId(), $entity->getImages());
        return $this;
    }

    protected function saveImages(int $id, int $ouId, ?array $data): S3
    {
        $type = static::TYPE_IMAGE;
        if (!$data) {
            return $this->removeImage($id, $ouId);
        }
        if (!$this->hasImageHashChanged($id, $data, $type)) {
            return $this;
        }
        $extension = $this->typeExtensions[$type];

        foreach ($data as $index => $itemData) {
            $this->fileStorage->write(
                $this->getS3KeyImages($ouId, $id, $index, $extension), $itemData
            );

            $this->statsIncrement(static::STAT_S3, [$ouId, 'save', $type]);
        }

        $this->saveImagesToCache($id, $ouId, $data, $type);

        return $this;
    }

    protected function hasImageHashChanged(int $id, array $data, string $type): bool
    {
        $redisKey = static::REDIS_KEY_HASHES_PREFIX . $type;
        $previousHashValue = $this->predisClient->hget($redisKey, $id);
        $str = '';
        foreach ($data as $itemData) {
            $str .= $itemData;
        }
        $currentHashValue = crc32($str);
        if ($previousHashValue == $currentHashValue) {
            return false;
        }

        $this->predisClient->hset($redisKey, $id, $currentHashValue);
        return true;
    }

    protected function saveImagesToCache(int $id, int $ouId, array $data, string $type): S3
    {
        $key = $this->getCacheKey($id, $type);
        foreach ($data as $index => $itemData) {
            $this->predisClient->hmsetex($key, static::REDIS_CACHE_EXPIRE_SEC, $index, $itemData);
            $this->statsIncrement(static::STAT_CACHE, [$ouId, 'save', $type]);
        }
        return $this;
    }

    protected function getS3KeyImages(int $ouId, int $id, ?int $imageIndex = null, ?string $extension = null): string
    {
        $key = ENVIRONMENT . '/' . $ouId . '/' . $id;

        if (!is_null($imageIndex)) {
            $key .= '/' . $imageIndex;
        }
        if (!is_null($extension)) {
            $key .= '.' . $extension;
        }

        return $key;
    }
}