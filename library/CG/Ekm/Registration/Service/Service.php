<?php
namespace CG\Ekm\Registration\Service;

use CG\Ekm\Registration\Mapper;
use CG\Ekm\Registration\Service as BaseService;
use CG\Ekm\Registration\StorageInterface;
use CG\Stdlib\Sites;

class Service extends BaseService
{
    public function __construct(
        StorageInterface $repository,
        Mapper $mapper,
        Sites $sites
    ) {
        parent::__construct($repository, $mapper, $sites);
    }

    public function save($entity)
    {
        $entity = parent::save($entity);
        $this->createEkmRegistrationGearmanJob($entity->getEkmUsername(), $entity->getToken());
        return $entity;
    }
}