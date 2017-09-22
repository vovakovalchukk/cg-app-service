<?php
namespace CG\Ekm\Registration\Request\Storage;

use CG\Ekm\Registration\Collection;
use CG\Ekm\Registration\Entity;
use CG\Ekm\Registration\Mapper;
use CG\Ekm\Registration\StorageInterface;
use CG\Http\Client\SaveEtagTrait;
use CG\Http\Client\FetchTrait;
use CG\Http\Client\RemoveTrait;
use CG\Http\Client\EntityTrait;
use CG\Http\Client\CollectionTrait;
use Guzzle\Http\Client;

class Api implements StorageInterface
{
    use SaveEtagTrait, RemoveTrait, FetchTrait, CollectionTrait, EntityTrait;

    const URL = '/ekmRegistration';

    protected $mapper;
    protected $client;

    public function __construct(Client $client, Mapper $mapper)
    {
        $this->setClient($client)
            ->setMapper($mapper);
    }

    public function fetchCollectionByPagination($limit, $page, array $id)
    {
        $query = http_build_query(
            ['page' => $page, 'limit' => $limit, 'id' => $id]
        );

        return $this->fetchCollection(
            $this->getUrl() . '?' . $query,
            'ekmRegistration',
            new Collection(Entity::class, __FUNCTION__, ['id' => $id])
        );
    }

    protected function getUrl()
    {
        return static::URL;
    }

    protected function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    protected function getMapper()
    {
        return $this->mapper;
    }

    protected function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    protected function getClient()
    {
        return $this->client;
    }
}