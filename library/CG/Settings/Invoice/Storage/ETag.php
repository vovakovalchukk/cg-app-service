<?php
namespace CG\Settings\Invoice\Storage;

use CG\ETag\SaveTrait;
use CG\ETag\FetchTrait;
use CG\ETag\RemoveTrait;
use CG\ETag\EntityRepository;
use CG\Settings\Invoice\StorageInterface;

class ETag extends EntityRepository implements StorageInterface
{
    use SaveTrait, FetchTrait, RemoveTrait;

    public function fetchCollectionByPagination($limit, $page)
    {
        return $this->getEntityStorage()->fetchCollectionByPaginationAsHal($limit, $page);
    }
}