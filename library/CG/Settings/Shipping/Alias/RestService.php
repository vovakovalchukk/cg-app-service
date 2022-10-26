<?php

namespace CG\Settings\Shipping\Alias;

use CG\Settings\Shipping\Alias\Rule\Collection as RuleCollection;
use CG\Settings\Shipping\Alias\Rule\RestService as RuleRestService;
use CG\Slim\Renderer\ResponseType\Hal;
use CG\Stdlib\Exception\Runtime\NotFound;

class RestService extends Service
{
    public const DEFAULT_LIMIT = 10;
    public const DEFAULT_PAGE = 1;

    protected $repository;
    protected $mapper;
    /** @var RuleRestService */
    protected $ruleRestService;

    public function __construct(
        StorageInterface $repository,
        Mapper $mapper,
        RuleRestService $ruleRestService
    ) {
        $this->ruleRestService = $ruleRestService;
        parent::__construct($repository, $mapper);
    }

    public function fetchAsHal($id): Hal
    {
        $entity = $this->fetch($id);
        /** @var Entity $entity */
        $this->fetchAndAddEmbedsToEntity($entity);
        return $this->mapper->toHal($entity);
    }

    public function fetchCollectionByFilterAsHal(Filter $filter): Hal
    {
        if (!$filter->getPage()) {
            $filter->setPage(static::DEFAULT_PAGE);
        }
        if (!$filter->getLimit()) {
            $filter->setLimit(static::DEFAULT_LIMIT);
        }
        $collection = $this->repository->fetchCollectionByFilter($filter);
        $this->fetchAndAddEmbedsToCollection($collection);
        return $this->mapper->collectionToHal(
            $collection,
            Mapper::URL,
            $filter->getLimit(),
            $filter->getPage(),
            $filter->toArray()
        );
    }

    protected function fetchAndAddEmbedsToCollection(Collection $collection)
    {
        try {
            $ruleCollection = $this->ruleRestService->fetchCollectionForAliases($collection);
        } catch (NotFound $e) {
            return;
        }
        /** @var Entity $entity */
        foreach ($collection as $entity) {
            /** @var RuleCollection $ruleCollectionByShippingAliasId */
            $ruleCollectionByShippingAliasId = $ruleCollection->getBy('shippingAliasId', $entity->getId());
            $entity->setRules($ruleCollectionByShippingAliasId);
        }
    }

    protected function fetchAndAddEmbedsToEntity(Entity $entity)
    {
        try {
            $ruleCollection = $this->ruleRestService->fetchCollectionForAlias($entity);
            $entity->setRules($ruleCollection);
        } catch (NotFound $e) {
            return;
        }
    }
}