<?php
namespace CG\Ekm\Registration\Service;

use CG\Ekm\Gearman\Generator\Registration\Action\Register as RegisterActionGenerator;
use CG\Ekm\Registration\Mapper;
use CG\Ekm\Registration\Service as BaseService;
use CG\Ekm\Registration\StorageInterface;
use CG\Stdlib\Sites;

class Service extends BaseService
{

    /** @var  RegisterActionGenerator $registerActionGenerator */
    protected $registerActionGenerator;

    public function __construct(
        StorageInterface $repository,
        Mapper $mapper,
        Sites $sites,
        RegisterActionGenerator $registerActionGenerator
    ) {
        parent::__construct($repository, $mapper, $sites);
        $this->registerActionGenerator = $registerActionGenerator;
    }

    public function save($entity)
    {
        $entity = parent::save($entity);
        $this->createRegistrationJob($entity->getEkmUsername(), $entity->getToken());
        return $entity;
    }

    protected function createRegistrationJob(string $ekmUsername, string $token): void
    {
        $this->registerActionGenerator->__invoke($ekmUsername, $token);
        return;
    }
}