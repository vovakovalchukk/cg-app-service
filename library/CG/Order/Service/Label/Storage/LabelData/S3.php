<?php
namespace CG\Order\Service\Label\Storage\LabelData;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use CG\Http\StatusCode;
use CG\Order\Service\Label\Storage\LabelDataInterface;
use GuzzleHttp\Exception\ClientException;
use Predis\Client as PredisClient;

class S3 implements LabelDataInterface
{
    const BUCKET = 'orderhub-labeldata';
    const TYPE_DOCUMENT = 'label';
    const TYPE_IMAGE = 'image';
    const REDIS_KEY_HASHES_PREFIX = 'OrderLabelDataHashes:';
    const REDIS_KEY_CACHE_PREFIX = 'CG_Order_Shared_Label_Entity_data:';
    const REDIS_CACHE_EXPIRE_SEC = 172800; // 2 days

    /** @var S3Client */
    protected $s3Client;
    /** @var PredisClient */
    protected $predisClient;

    protected $typeExtensions = [
        self::TYPE_DOCUMENT => 'pdf',
        self::TYPE_IMAGE    => 'png',
    ];

    public function __construct(S3Client $s3Client, PredisClient $predisClient)
    {
        $this->s3Client = $s3Client;
        $this->predisClient = $predisClient;
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
        $cached = $this->fetchFromCache($id, $type);
        if ($cached) {
            return $cached;
        }

        try {
            $extension = $this->typeExtensions[$type];
            $result = $this->s3Client->getObject([
                'Bucket' => static::BUCKET,
                'Key'    => $this->getS3Key($ouId, $id, $extension),
            ]);
            $data = (string)$result['Body'];
            $this->saveToCache($id, $data, $type);
            return $data;

        } catch (S3Exception $e) {
            if ($e->getStatusCode() != StatusCode::NOT_FOUND) {
                throw $e;
            }
            return null;
        }
    }

    protected function fetchFromCache(int $id, string $type): ?string
    {
        $key = $this->getCacheKey($id, $type);
        return $this->predisClient->get($key);
    }

    public function remove($entity)
    {
        $this->removeDocument($entity->getId(), $entity->getOrganisationUnitId());
        $this->removeImage($entity->getId(), $entity->getOrganisationUnitId());
        return $this;
    }

    protected function removeDocument(int $id, int $ouId)
    {
        return $this->removeType($id, $ouId, static::TYPE_DOCUMENT);
    }

    protected function removeImage(int $id, int $ouId)
    {
        return $this->removeType($id, $ouId, static::TYPE_IMAGE);
    }

    protected function removeType(int $id, int $ouId, string $type)
    {
        try {
            $extension = $this->typeExtensions[$type];
            $result = $this->s3Client->deleteObject([
                'Bucket' => static::BUCKET,
                'Key'    => $this->getS3Key($ouId, $id, $extension),
            ]);

        } catch (S3Exception $e) {
            if ($e->getStatusCode() != StatusCode::NOT_FOUND) {
                throw $e;
            }
            // No-op
        }
        $this->removeHash($id, $type);
        $this->removeFromcache($id, $type);
        return $this;
    }

    protected function removeHash(int $id, string $type)
    {
        $redisKey = static::REDIS_KEY_HASHES_PREFIX . $type;
        $this->predisClient->hdel($redisKey, $id);
        return $this;
    }

    protected function removeFromCache(int $id, string $type)
    {
        $key = $this->getCacheKey($id, $type);
        $this->predisClient->del($key);
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
        return $this->saveType($id, $ouId, $data, static::TYPE_DOCUMENT);
    }

    protected function saveImage(int $id, int $ouId, ?string $data)
    {
        return $this->saveType($id, $ouId, $data, static::TYPE_IMAGE);
    }

    protected function saveType(int $id, int $ouId, string $data, string $type)
    {
        if (!$data) {
            return $this->removeType($id, $ouId, $type);
        }
        if (!$this->hasHashChanged($id, $data, $type)) {
            return $this;
        }
        $extension = $this->typeExtensions[$type];
        $result = $this->s3Client->putObject([
            'Bucket' => static::BUCKET,
            'Key'    => $this->getS3Key($ouId, $id, $extension),
            'Body'   => $data
        ]);
        $this->saveToCache($id, $data, $type);
        return $this;
    }

    protected function hasHashChanged(int $id, string $data, string $type): bool
    {
        $redisKey = static::REDIS_KEY_HASHES_PREFIX . $type;
        $previousHashValue = $this->predisClient->hget($redisKey, $id);
        $currentHashValue = crc32($data);
        if ($previousHashValue == $currentHashValue) {
            return false;
        }

        $this->predisClient->hset($redisKey, $id, $currentHashValue);
        return true;
    }

    protected function saveToCache(int $id, string $data, string $type)
    {
        $key = $this->getCacheKey($id, $type);
        $this->predisClient->setex($key, static::REDIS_CACHE_EXPIRE_SEC, $data);
        return $this;
    }

    protected function getS3Key(int $ouId, int $id, string $extension): string
    {
        return ENVIRONMENT . '/' . $ouId . '/' . $id . '.' . $extension;
    }

    protected function getCacheKey(int $id, string $type): string
    {
        return static::REDIS_KEY_CACHE_PREFIX . $type . ':' . $id;
    }
}