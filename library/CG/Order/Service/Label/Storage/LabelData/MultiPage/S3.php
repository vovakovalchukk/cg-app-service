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

class S3 extends LabelDataS3 implements LabelDataInterface, StatsAwareInterface, LoggerAwareInterface
{
    use StatsTrait;
    use LogTrait;

    protected const LOG_CODE = 'LabelDataS3Storage';

    const BUCKET = 'orderhub-labeldata';
    const TYPE_DOCUMENT = 'label';
    const TYPE_IMAGE = 'image';
    const REDIS_KEY_HASHES_PREFIX = 'OrderLabelDataHashes:';
    const REDIS_KEY_CACHE_PREFIX = 'CG_Order_Shared_Label_Entity_data:';
    const REDIS_CACHE_EXPIRE_SEC = 172800; // 2 days
    const STAT_S3 = 'orderlabel.storage.s3.ou-%d.%s.%s';
    const STAT_CACHE = 'orderlabel.storage.cache.ou-%d.%s.%s';

    /** @var S3Adapter */
    protected $fileStorage;
    /** @var PredisClient */
    protected $predisClient;

    protected $typeExtensions = [
        self::TYPE_DOCUMENT => 'pdf',
        self::TYPE_IMAGE    => 'png',
    ];

    public function __construct(S3Adapter $s3FileStorage, PredisClient $predisClient)
    {
        $this->fileStorage = $s3FileStorage;
        $this->predisClient = $predisClient;
        $this->predisClient->getProfile()->defineCommand('hmsetex', HmsetEx::class);
    }

    public function fetch(int $id, int $ouId): array
    {
        return [
            static::TYPE_DOCUMENT => $this->fetchDocument($id, $ouId),
            static::TYPE_IMAGE    => $this->fetchImage($id, $ouId),
        ];
    }

    protected function fetchDocument(int $id, int $ouId): ?string
    {
        return $this->fetchType($id, $ouId, static::TYPE_DOCUMENT);
    }

    protected function fetchImage(int $id, int $ouId): ?string
    {
        return $this->fetchType($id, $ouId, static::TYPE_IMAGE);
    }

    protected function fetchType(int $id, int $ouId, string $type): ?string
    {
        $cached = $this->fetchFromCache($id, $ouId, $type);
        if ($cached) {
            return $cached;
        }

        $extension = $this->typeExtensions[$type];
        try {
            $result = $this->fileStorage->read($this->getS3Key($ouId, $id, $extension));
            $this->saveImagesToCache($id, $ouId, $result->getBody(), $type);
            $this->statsIncrement(static::STAT_S3, [$ouId, 'fetch', $type]);
            return $result->getBody();
        } catch (NotFound $e) {
            return null;
        }
    }

    protected function fetchFromCache(int $id, int $ouId, string $type): ?string
    {
        $key = $this->getCacheKey($id, $type);
        $data = $this->predisClient->get($key);
        if ($data) {
            $this->statsIncrement(static::STAT_CACHE, [$ouId, 'fetch', $type]);
        }
        return $data;
    }

    public function remove($entity)
    {
        $this->logDebug('About to remove orderLabelDocument entity data in %s', [__METHOD__], static::LOG_CODE);
        $this->removeDocument($entity->getId(), $entity->getOrganisationUnitId());
        $this->removeImage($entity->getId(), $entity->getOrganisationUnitId());
        $this->logDebug('Completed removing orderLabelDocument entity data in %s', [__METHOD__], static::LOG_CODE);
        return $this;
    }

//    protected function removeDocument(int $id, int $ouId)
//    {
//        return $this->removeType($id, $ouId, static::TYPE_DOCUMENT);
//    }

    protected function removeImage(int $id, int $ouId)
    {
//        return $this->removeType($id, $ouId, static::TYPE_IMAGE);

        $type = static::TYPE_IMAGE;
        try {
            $extension = $this->typeExtensions[$type];
            $this->fileStorage->delete($this->getS3Key($ouId, $id, $extension));
            $this->statsIncrement(static::STAT_S3, [$ouId, 'remove', $type]);
        } catch (NotFound $e) {
            // No-op
        }
        $this->removeHash($id, $type);
        $this->removeFromCache($id, $ouId, $type);
        return $this;
    }

//    protected function removeType(int $id, int $ouId, string $type)
//    {
//        try {
//            $extension = $this->typeExtensions[$type];
//            $this->fileStorage->delete($this->getS3Key($ouId, $id, $extension));
//            $this->statsIncrement(static::STAT_S3, [$ouId, 'remove', $type]);
//        } catch (NotFound $e) {
//            // No-op
//        }
//        $this->removeHash($id, $type);
//        $this->removeFromCache($id, $ouId, $type);
//        return $this;
//    }

    protected function removeHash(int $id, string $type)
    {
        $redisKey = static::REDIS_KEY_HASHES_PREFIX . $type;
        $this->predisClient->hdel($redisKey, $id);
        return $this;
    }

    protected function removeFromCache(int $id, int $ouId, string $type)
    {
        $key = $this->getCacheKey($id, $type);
        $this->predisClient->del($key);
        $this->statsIncrement(static::STAT_CACHE, [$ouId, 'remove', $type]);
        return $this;
    }

    public function save($entity)
    {
        $this->saveDocument($entity->getId(), $entity->getOrganisationUnitId(), $entity->getLabel());
        $this->saveImage($entity->getId(), $entity->getOrganisationUnitId(), $entity->getImage());
        return $this;
    }

    protected function saveDocument(int $id, int $ouId, ?string $data)
    {
        return parent::saveType($id, $ouId, $data, static::TYPE_DOCUMENT);
    }

    protected function saveImage(int $id, int $ouId, ?array $data)
    {
//        return $this->saveType($id, $ouId, $data, static::TYPE_IMAGE);
        $type = static::TYPE_IMAGE;

        if (!$data) {
            return $this->removeType($id, $ouId, $type);
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

//    protected function saveType(int $id, int $ouId, ?string $data, string $type)
//    {
//        if (!$data) {
//            return $this->removeType($id, $ouId, $type);
//        }
//        if (!$this->hasHashChanged($id, $data, $type)) {
//            return $this;
//        }
//        $extension = $this->typeExtensions[$type];
//
//        $this->fileStorage->write(
//            $this->getS3KeyImages($ouId, $id, $extension), $data
//        );
//
//        $this->statsIncrement(static::STAT_S3, [$ouId, 'save', $type]);
//        $this->saveImagesToCache($id, $ouId, $data, $type);
//        return $this;
//    }

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

    protected function saveImagesToCache(int $id, int $ouId, array $data, string $type)
    {
        $key = $this->getCacheKey($id, $type);
        foreach ($data as $index => $itemData) {
            $this->predisClient->hmsetex($key, static::REDIS_CACHE_EXPIRE_SEC, $index, $itemData);
            $this->statsIncrement(static::STAT_CACHE, [$ouId, 'save', $type]);
        }
        return $this;
    }

    protected function getS3KeyImages(int $ouId, int $id, int $imageIndex, string $extension): string
    {
        return ENVIRONMENT . '/' . $ouId . '/' . $id . '/' . $imageIndex . '.' . $extension;
    }

//    protected function getCacheKey(int $id, string $type): string
//    {
//        return static::REDIS_KEY_CACHE_PREFIX . $type . ':' . $id;
//    }
}