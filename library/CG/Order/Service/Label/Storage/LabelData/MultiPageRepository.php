<?php
namespace CG\Order\Service\Label\Storage\LabelData;

use CG\Order\Service\Label\Storage\LabelDataInterface;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Order\Service\Label\Storage\LabelData\S3 as SinglePageS3Storage;

class MultiPageRepository implements LabelDataInterface, StatsAwareInterface, LoggerAwareInterface
{
    use StatsTrait;
    use LogTrait;

    protected $multiPageStorage;
    protected $singlePageStorage;

    public function __construct(LabelDataInterface $multiPageStorage, LabelDataInterface $singlePageStorage)
    {
        $this->multiPageStorage = $multiPageStorage;
        $this->singlePageStorage = $singlePageStorage;
    }

    public function fetch(int $id, int $ouId): array
    {
        $data = $this->multiPageStorage->fetch($id, $ouId);
        if (!empty($data[SinglePageS3Storage::TYPE_IMAGE])) {
            return $data;
        }

        $data = $this->singlePageStorage->fetch($id, $ouId);
        if (!is_null($data[SinglePageS3Storage::TYPE_IMAGE])) {
            $data[SinglePageS3Storage::TYPE_IMAGE] = [$data[SinglePageS3Storage::TYPE_IMAGE]];
        }

        return $data;
    }

    public function remove($entity)
    {
        $this->singlePageStorage->remove($entity);
        $this->multiPageStorage->remove($entity);

        return $this;
    }

    public function save($entity)
    {
        $this->multiPageStorage->save($entity);
        return $this;
    }
}