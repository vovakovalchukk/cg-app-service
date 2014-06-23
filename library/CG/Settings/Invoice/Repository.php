<?php
namespace CG\Settings\Invoice;

use CG\Stdlib\Repository\SaveTrait;
use CG\Stdlib\Repository\FetchEntityTrait;
use CG\Stdlib\Repository\RemoveTrait;
use CG\Stdlib\Repository\FetchTrait;
use CG\Stdlib\Repository\Collection\SaveTrait as SaveTraitCollection;
use CG\Stdlib\Repository\FetchEntityCollectionTrait;

class Repository implements StorageInterface
{
    use FetchEntityCollectionTrait, SaveTrait, SaveTraitCollection, FetchEntityTrait, RemoveTrait, FetchTrait;

    protected $storage;
    protected $repository;

    public function __construct(StorageInterface $storage, StorageInterface $repository)
    {
        $this->setStorage($storage)
            ->setRepository($repository);
    }

    public function fetchCollectionByPagination($limit, $page)
    {
        return $this->fetchEntityCollection(__FUNCTION__, compact('limit', 'page'));
    }

    public function setRepository(StorageInterface $repository)
    {
        $this->repository = $repository;
        return $this;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    public function getStorage()
    {
        return $this->storage;
    }
}