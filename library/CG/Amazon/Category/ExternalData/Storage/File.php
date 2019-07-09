<?php
namespace CG\Amazon\Category\ExternalData\Storage;

use CG\Amazon\Category\ExternalData\Data;
use CG\Amazon\Category\ExternalData\StorageInterface;
use CG\FileStorage\AdapterInterface as FileStorage;
use CG\Stdlib\Exception\Runtime\NotFound;

class File implements StorageInterface
{
    /** @var FileStorage */
    protected $fileStorage;

    public function __construct(FileStorage $fileStorage)
    {
        $this->fileStorage = $fileStorage;
    }

    protected function getFilePath(int $categoryId): string
    {
        return ENVIRONMENT . '/' . $categoryId . '.json';
    }

    public function fetch(int $categoryId): Data
    {
        try {
            $data = json_decode(
                $this->fileStorage->read($this->getFilePath($categoryId))->getBody(),
                true
            );
        } catch (NotFound $exception) {
            $data = [];
        }
        return Data::fromArray($data);
    }

    public function save(int $categoryId, Data $data): void
    {
        $this->fileStorage->write($this->getFilePath($categoryId), json_encode($data->toArray()));
    }

    public function remove(int $categoryId): void
    {
        $this->fileStorage->delete($this->getFilePath($categoryId));
    }
}