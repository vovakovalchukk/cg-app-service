<?php
namespace CG\App\Service\Event\Storage;

use CG\ETag\SaveTrait;
use CG\ETag\FetchTrait;
use CG\ETag\RemoveTrait;
use CG\ETag\EntityRepository;
use CG\App\Service\Event\StorageInterface;

class ETag extends EntityRepository implements StorageInterface
{
    use SaveTrait, FetchTrait, RemoveTrait;

    public function fetchCollectionByServiceId($limit, $page, $serviceId)
    {
        return $this->getEntityStorage()->fetchCollectionByServiceId($limit, $page, $serviceId);
    }

    public function fetchCollectionByServiceIds(array $serviceIds)
    {
        return $this->getEntityStorage()->fetchCollectionByServiceIds($serviceIds);
    }
}