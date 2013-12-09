<?php
namespace CG\App\Service;

use CG\Stdlib\Repository\FetchInterface;
use CG\Stdlib\Repository\SaveInterface;
use CG\Stdlib\Repository\RemoveInterface;
use CG\Stdlib\Repository\FetchEntityTrait;
use CG\Stdlib\Repository\FetchTrait;
use CG\Stdlib\Repository\FetchEntityCollectionTrait;
use CG\Stdlib\Repository\SaveTrait;
use CG\Stdlib\Repository\RemoveTrait;
use CG\App\Service\StorageInterface;

class Repository implements FetchInterface, SaveInterface, RemoveInterface, StorageInterface
{
    use FetchEntityTrait, FetchTrait, FetchEntityCollectionTrait, SaveTrait, RemoveTrait;

    protected $storage;
    protected $repository;

    public function __construct(StorageInterface $storage, StorageInterface $repository)
    {
        $this->setStorage($storage)
             ->setRepository($repository);
    }

    public function fetchCollectionWithPagination($limit, $page)
    {
        return $this->fetchEntityCollection(__FUNCTION__, func_get_args());
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

    public function setRepository(StorageInterface $repository)
    {
        $this->repository = $repository;
        return $this;
    }

    public function getRepository()
    {
        return $this->repository;
    }
}
