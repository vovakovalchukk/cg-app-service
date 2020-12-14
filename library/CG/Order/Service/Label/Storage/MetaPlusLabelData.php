<?php
namespace CG\Order\Service\Label\Storage;

use CG\Order\Service\Label\Storage\LabelDataInterface;
use CG\Order\Service\Label\Storage\MetaDataInterface;
use CG\Order\Shared\Label\Collection;
use CG\Order\Shared\Label\Entity;
use CG\Order\Shared\Label\Filter;
use CG\Order\Shared\Label\StorageInterface;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class MetaPlusLabelData implements StorageInterface, LoggerAwareInterface
{
    use LogTrait;

    protected const LOG_CODE = 'MetaPlusLabelDataStorage';

    /** @var MetaDataInterface */
    protected $metaDataStorage;
    /** @var LabelDataInterface */
    protected $labelDataStorage;

    public function __construct(MetaDataInterface $metaDataStorage, LabelDataInterface $labelDataStorage)
    {
        $this->metaDataStorage = $metaDataStorage;
        $this->labelDataStorage = $labelDataStorage;
    }

    /**
     * @return Entity
     */
    public function fetch($id)
    {
        /* @var $entity Entity */
        $entity = $this->metaDataStorage->fetch($id);
        $labelData = $this->labelDataStorage->fetch($entity->getId(), $entity->getOrganisationUnitId());
        $entity->setLabel($labelData['label'])
            ->setImage($labelData['image'][0] ?? null)
            ->setImages($labelData['image'] ?? []);
        return $entity;
    }

    /**
     * @return Collection
     */
    public function fetchCollectionByFilter(Filter $filter)
    {
        $collection = $this->metaDataStorage->fetchCollectionByFilter($filter);
        foreach ($collection as $entity) {
            $labelData = $this->labelDataStorage->fetch($entity->getId(), $entity->getOrganisationUnitId());
            $entity->setLabel($labelData['label'])
                ->setImage($labelData['image'][0] ?? null)
                ->setImages($labelData['image'] ?? []);
        }
        return $collection;
    }

    public function remove($entity)
    {
        $this->logDebug('About to remove orderLabel entity data in %s', [__METHOD__], static::LOG_CODE);
        $this->metaDataStorage->remove($entity);
        $this->labelDataStorage->remove($entity);
        $this->logDebug('Completed removing orderLabel entity data in %s', [__METHOD__], static::LOG_CODE);
        return $this;
    }

    public function save($entity)
    {
        // Catch entities loaded out of the old Mongo storage which will have string IDs
        if ($entity->getId() && !is_numeric($entity->getId())) {
            $entity->setMongoId($entity->getId());
            $entity->setId(null);
        }
        $this->metaDataStorage->save($entity);
        $this->labelDataStorage->save($entity);
        return $entity;
    }

}
