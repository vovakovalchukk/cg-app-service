<?php
namespace CG\Amazon\Category\ExternalData\Storage;

use CG\Amazon\Category\ExternalData\Data;
use CG\Amazon\Category\ExternalData\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Predis\Client;

class Cache implements StorageInterface
{
    const KEY_PREFIX = 'Amazon::CategoryExternalData::';

    /** @var Client */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    protected function getKey(int $categoryId): string
    {
        return static::KEY_PREFIX . $categoryId;
    }

    public function fetch(int $categoryId): Data
    {
        $data = $this->client->get($this->getKey($categoryId));
        if ($data === null) {
            throw new NotFound('No amazon category external data found for categoryId');
        }
        return unserialize($data);
    }

    public function save(int $categoryId, Data $data): void
    {
        $this->client->set($this->getKey($categoryId), serialize($data));
    }

    public function remove(int $categoryId): void
    {
        $this->client->del($this->getKey($categoryId));
    }
}