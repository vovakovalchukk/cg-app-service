<?php
namespace CG\App\Service;

use CG\Stdlib\Repository\FetchInterface;
use CG\Stdlib\Repository\FetchByIdsInterface;
use CG\Stdlib\Repository\FetchAllInterface;
use CG\Stdlib\Repository\SaveInterface;
use CG\Stdlib\Repository\RemoveInterface;
use CG\Stdlib\Repository\FetchEntityTrait;
use CG\Stdlib\Repository\FetchTrait;
use CG\Stdlib\Repository\FetchEntityCollectionTrait;
use CG\Stdlib\Repository\FetchByIdsTrait;
use CG\Stdlib\Repository\FetchAllTrait;
use CG\Stdlib\Repository\SaveTrait;
use CG\Stdlib\Repository\RemoveTrait;

class Repository implements
    FetchInterface, FetchByIdsInterface, FetchAllInterface, SaveInterface, RemoveInterface
{
    use FetchEntityTrait, FetchTrait, FetchEntityCollectionTrait, FetchByIdsTrait, FetchAllTrait, SaveTrait, RemoveTrait;

    protected $storage;
    protected $repository;

    public function __construct(Storage $storage, Repository $repository = null)
    {
        $this->setStorage($storage);
        if ($repository) {
            $this->setRepository($repository);
        }
    }

    public function setStorage(Storage $storage)
    {
        $this->storage = $storage;
    }

    public function getStorage()
    {
        return $this->storage;
    }

    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function getRepository()
    {
        return $this->repository;
    }
}
