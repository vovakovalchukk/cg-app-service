<?php
namespace CG\App\Service\Storage;

use CG\ETag\SaveTrait;
use CG\ETag\FetchTrait;
use CG\ETag\RemoveTrait;
use CG\ETag\EntityRepository;
use CG\App\Service\StorageInterface;

class ETag extends EntityRepository implements StorageInterface
{
    use SaveTrait, FetchTrait, RemoveTrait;

    public function fetchCollectionWithPagination($limit, $page)
    {
        return $this->getEntityStorage()->fetchCollection($limit, $page);
    }
}