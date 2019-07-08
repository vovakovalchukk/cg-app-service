<?php
namespace CG\Amazon\Category\ExternalData;

use CG\Stdlib\Exception\Runtime\NotFound;

class Repository implements StorageInterface
{
    /** @var StorageInterface */
    protected $storage;
    /** @var StorageInterface */
    protected $repository;

    public function __construct(StorageInterface $storage, StorageInterface $repository)
    {
        $this->storage = $storage;
        $this->repository = $repository;
    }

    public function fetch(int $categoryId): Data
    {
        echo __METHOD__."\n";
        try {
            return $this->storage->fetch($categoryId);
        } catch (NotFound $exception) {
            $data = $this->repository->fetch($categoryId);
//            $this->storage->save($categoryId, $data);
            return $data;
        }
    }

    public function save(int $categoryId, Data $data): void
    {
        echo __METHOD__."\n";

        $this->repository->save($categoryId, $data);
        $this->storage->save($categoryId, $data);
    }

    public function remove(int $categoryId): void
    {
        $this->repository->remove($categoryId);
        $this->storage->remove($categoryId);
    }
}