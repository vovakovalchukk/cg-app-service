<?php
namespace CG\Order\Service\Label\Storage\LabelData;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use CG\Http\StatusCode;
use CG\Order\Service\Label\Storage\LabelDataInterface;
use GuzzleHttp\Exception\ClientException;

class S3 implements LabelDataInterface
{
    const BUCKET = 'orderhub-labeldata';
    const EXT_DOCUMENT = 'pdf';
    const EXT_IMAGE = 'png';

    /** @var S3Client */
    protected $s3Client;

    public function __construct(S3Client $s3Client)
    {
        $this->s3Client = $s3Client;
    }

    public function fetch(int $id, int $ouId): array
    {
        return [
            'label' => $this->fetchDocument($id, $ouId),
            'image' => $this->fetchImage($id, $ouId),
        ];
    }

    protected function fetchDocument(int $id, int $ouId): ?string
    {
        return $this->fetchType($id, $ouId, static::EXT_DOCUMENT);
    }

    protected function fetchImage(int $id, int $ouId): ?string
    {
        return $this->fetchType($id, $ouId, static::EXT_IMAGE);
    }

    protected function fetchType(int $id, int $ouId, string $extension): ?string
    {
        try {
            $result = $this->s3Client->getObject([
                'Bucket' => static::BUCKET,
                'Key'    => $this->getKey($ouId, $id, $extension),
            ]);
            return (string)$result['Body'];

        } catch (S3Exception $e) {
            if ($e->getStatusCode() != StatusCode::NOT_FOUND) {
                throw $e;
            }
            return null;
        }
    }

    public function remove($entity)
    {
        $this->removeDocument($entity->getId(), $entity->getOrganisationUnitId());
        $this->removeImage($entity->getId(), $entity->getOrganisationUnitId());
        return $this;
    }

    protected function removeDocument(int $id, int $ouId)
    {
        return $this->removeType($id, $ouId, static::EXT_DOCUMENT);
    }

    protected function removeImage(int $id, int $ouId)
    {
        return $this->removeType($id, $ouId, static::EXT_IMAGE);
    }

    protected function removeType(int $id, int $ouId, string $extension)
    {
        try {
            $result = $this->s3Client->deleteObject([
                'Bucket' => static::BUCKET,
                'Key'    => $this->getKey($ouId, $id, $extension),
            ]);

        } catch (S3Exception $e) {
            if ($e->getStatusCode() != StatusCode::NOT_FOUND) {
                throw $e;
            }
            // No-op
        }
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
        if (!$data) {
            return $this->removeDocument($id, $ouId);
        }
        return $this->saveType($id, $ouId, $data, static::EXT_DOCUMENT);
    }

    protected function saveImage(int $id, int $ouId, ?string $data)
    {
        if (!$data) {
            return $this->removeImage($id, $ouId);
        }
        return $this->saveType($id, $ouId, $data, static::EXT_IMAGE);
    }

    protected function saveType(int $id, int $ouId, string $data, string $extension)
    {
        $result = $this->s3Client->putObject([
            'Bucket' => static::BUCKET,
            'Key'    => $this->getKey($ouId, $id, $extension),
            'Body'   => $data
        ]);
        return $this;
    }

    protected function getKey(int $ouId, int $id, string $extension): string
    {
        return ENVIRONMENT . '/' . $ouId . '/' . $id . '.' . $extension;
    }
}