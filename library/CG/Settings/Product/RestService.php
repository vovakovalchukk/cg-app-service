<?php
namespace CG\Settings\Product;

use CG\Settings\Product\Entity;
use CG\Settings\Product\Service;
use CG\Stock\Gearman\Workload\UpdateIncludePurchaseOrderFromDefault as UpdateIncludePurchaseOrderFromDefaultWorkload;
use GearmanClient;

class RestService extends Service
{
    /** @var GearmanClient */
    protected $gearmanClient;

    public function __construct(StorageInterface $repository, Mapper $mapper, GearmanClient $gearmanClient)
    {
        parent::__construct($repository, $mapper);
        $this->gearmanClient = $gearmanClient;
    }

    /**
     * @param Entity $entity
     */
    public function save($entity)
    {
        $existingEntity = $this->fetch($entity->getId());
        $hal = parent::save($entity);
        $this->handleIncludePurchaseOrdersInAvailableChange($existingEntity, $entity);
        return $hal;
    }

    protected function handleIncludePurchaseOrdersInAvailableChange(Entity $existingEntity, Entity $newEntity): void
    {
        if ($existingEntity->isIncludePurchaseOrdersInAvailable() == $newEntity->isIncludePurchaseOrdersInAvailable()) {
            return;
        }
        $workload = new UpdateIncludePurchaseOrderFromDefaultWorkload($newEntity->getOrganisationUnitId(), $newEntity->isIncludePurchaseOrdersInAvailable());
        $this->gearmanClient->doBackground(UpdateIncludePurchaseOrderFromDefaultWorkload::FUNCTION_NAME, serialize($workload));
    }
}