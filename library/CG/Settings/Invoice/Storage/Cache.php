<?php
namespace CG\Settings\Invoice\Storage;

use CG\Settings\Invoice\Entity;
use CG\Settings\Invoice\StorageInterface;
use CG\Cache\Storage\CollectionTrait;
use CG\Cache\Storage\SaveTrait;
use CG\Cache\Storage\RemoveTrait;
use CG\Cache\Storage\FetchTrait;
use CG\Cache\Storage\RemoveByFieldTrait;
use CG\Cache\CacheAbstract;
use CG\Stdlib\Exception\Runtime\NotFound;

class Cache extends CacheAbstract implements StorageInterface
{
    use CollectionTrait, SaveTrait, RemoveTrait, RemoveByFieldTrait, FetchTrait;

    public function fetchCollectionByPagination($limit, $page)
    {
        throw new NotFound('TODO');
    }

    public function getEntityClass()
    {
        return Entity::class;
    }
}