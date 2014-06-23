<?php
namespace CG\Settings\Invoice\Storage;

use CG\ETag\SaveTrait;
use CG\ETag\FetchTrait;
use CG\ETag\RemoveTrait;
use CG\ETag\EntityRepository;
use CG\Settings\Invoice\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;

class ETag extends EntityRepository implements StorageInterface
{
    use SaveTrait, FetchTrait, RemoveTrait;

//    public function fetchCollectionByPagination($limit, $page, array $id, array $organisationUnitId, array $type)
//    {
//        return $this->getEntityStorage()->fetchCollectionByPaginationAsHal($limit, $page, $id, $organisationUnitId, $type);
//    }
}