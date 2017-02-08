<?php
namespace CG\Order\Service\Label\Storage;

use CG\Order\Service\Label\Storage\LabelDataInterface;
use CG\Order\Service\Label\Storage\MetaDataInterface;
use CG\Order\Shared\Label\Collection;
use CG\Order\Shared\Label\Entity;
use CG\Order\Shared\Label\Filter;
use CG\Order\Shared\Label\StorageInterface;

class MetaPlusLabelData implements StorageInterface
{
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
        $entity = $this->metaDataStorage->fetch($id);
        $labelData = $this->labelDataStorage->fetch($entity->getId(), $entity->getOrganisationUnitId());
        $entity->setLabel($labelData['label'])
            ->setImage($labelData['image']);
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
                ->setImage($labelData['image']);
        }
        return $collection;
    }

    public function remove($entity)
    {
        $this->metaDataStorage->remove($entity);
        $this->labelDataStorage->remove($entity);
        return $this;
    }

    public function save($entity)
    {
        $this->metaDataStorage->save($entity);
        $this->labelDataStorage->save($entity);
        return $entity;
    }

}
