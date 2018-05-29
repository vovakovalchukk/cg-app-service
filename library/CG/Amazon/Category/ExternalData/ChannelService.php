<?php
namespace CG\Amazon\Category\ExternalData;

use CG\Product\Category\ExternalData\ChannelServiceInterface;
use CG\Product\Category\ExternalData\DataInterface;
use CG\Product\Category\ExternalData\Entity as ExternalData;

class ChannelService implements ChannelServiceInterface
{
    /** @var StorageInterface */
    protected $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function fetch(ExternalData $externalData): DataInterface
    {
        return $this->storage->fetch($externalData->getCategoryId());
    }

    public function save(int $categoryId, DataInterface $externalData)
    {
        if ($externalData instanceof Data) {
            $this->storage->save($categoryId, $externalData);
        } else {
            $this->storage->remove($categoryId);
        }
    }

    public function update(int $categoryId, DataInterface $externalData)
    {
        $this->save($categoryId, $externalData);
    }

    public function remove(int $categoryId, DataInterface $externalData)
    {
        $this->storage->remove($categoryId);
    }
}