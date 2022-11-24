<?php

namespace CG\Settings\Shipping\Alias\Rule\Storage;

use CG\Cache\CacheAbstract;
use CG\Cache\Storage\CollectionTrait;
use CG\Cache\Storage\RemoveByFieldTrait;
use CG\Cache\Storage\RemoveTrait;
use CG\Cache\Storage\SaveTrait;
use CG\Cache\Strategy\CollectionInterface as CollectionStrategy;
use CG\Cache\Strategy\EntityInterface as EntityStrategy;
use CG\Settings\Shipping\Alias\Rule\Filter;
use CG\Settings\Shipping\Alias\Rule\Mapper;
use CG\Settings\Shipping\Alias\Rule\Collection;
use CG\Settings\Shipping\Alias\Rule\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Runtime\Storage\Failure as StorageFailure;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class Cache extends CacheAbstract implements StorageInterface, LoggerAwareInterface
{
    use CollectionTrait;
    use SaveTrait;
    use RemoveTrait;
    use RemoveByFieldTrait;
    use LogTrait;

    public function __construct(Mapper $mapper, EntityStrategy $entityStrategy, CollectionStrategy $collectionStrategy)
    {
        parent::__construct($mapper, $entityStrategy, $collectionStrategy);
    }

    public function fetchCollectionByFilter(Filter $filter): Collection
    {
        $collection = new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray());
        return $this->fetchCollection($collection);
    }

    public function fetch($id, $shippingAliasId)
    {
        $cacheKey = $this->getEntityStrategy()->generateKeyForEntity($this->getEntityClass(), $id);
        try {
            return $this->getEntityStrategy()->get($cacheKey);
        } catch (StorageFailure $exception) {
            $info = [
                'Storage' => get_class($this),
                'Entity' => $this->getEntityClass(),
            ];

            if ($exception->isStoragePersistent()) {
                if (is_callable([$this, 'logPretty'])) {
                    $this->logPretty('Failed to fetch entity from persistent cache, can not continue.', $info, 'error', [], get_class($this) . ' - ' . $exception->getLogCode());
                }
                throw $exception;
            }

            if (is_callable([$this, 'logPretty'])) {
                $this->logPretty('Failed to fetch entity from non-persistent cache, ignoring. This may result in unexpected behaviour.', $info, 'warning', [], get_class($this) . ' - ' . $exception->getLogCode());
            }

            throw new NotFound(
                'Failed to fetch entity with id ' . $id . ' and shippingAliasId ' . $shippingAliasId . ' (' . $this->getEntityClass() . ')',
                404,
                $exception,
                get_class($this) . ' - ' . $exception->getLogCode()
            );
        }
    }
}