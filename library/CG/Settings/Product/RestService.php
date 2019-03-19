<?php
namespace CG\Settings\Product;

use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stock\Gearman\Generator\UpdateLowStockThresholdFromDefault as UpdateLowStockThresholdFromDefaultGenerator;
use CG\Stock\Gearman\Workload\UpdateIncludePurchaseOrderFromDefault as UpdateIncludePurchaseOrderFromDefaultWorkload;
use GearmanClient;

class RestService extends Service implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'SettingsProductRestService';
    const LOG_INCLUDE_PO_CHANGED = 'The includePurchaseOrdersInAvailable flag for OU %d has changed to %s, will create jobs to update Stock';
    const LOG_INCLUDE_LOW_STOCK_THRESHOLD_CHANGED = 'The low stock threshold flag for OU %d has changed to %s, will create jobs to update Stock';

    /** @var GearmanClient */
    protected $gearmanClient;
    /** @var UpdateLowStockThresholdFromDefaultGenerator */
    protected $updateLowStockThresholdFromDefaultGenerator;

    public function __construct(
        StorageInterface $repository,
        Mapper $mapper,
        GearmanClient $gearmanClient,
        UpdateLowStockThresholdFromDefaultGenerator $updateLowStockThresholdFromDefaultGenerator
    ) {
        parent::__construct($repository, $mapper);
        $this->gearmanClient = $gearmanClient;
        $this->updateLowStockThresholdFromDefaultGenerator = $updateLowStockThresholdFromDefaultGenerator;
    }

    /**
     * @param Entity $entity
     */
    public function save($entity)
    {
        $existingEntity = $this->fetch($entity->getId());
        $hal = parent::save($entity);
        $this->handleIncludePurchaseOrdersInAvailableChange($existingEntity, $entity);
        $this->handleLowStockThresholdChange($existingEntity, $entity);
        return $hal;
    }

    protected function handleIncludePurchaseOrdersInAvailableChange(Entity $existingEntity, Entity $newEntity): void
    {
        if ($existingEntity->isIncludePurchaseOrdersInAvailable() == $newEntity->isIncludePurchaseOrdersInAvailable()) {
            return;
        }
        $this->logDebug(static::LOG_INCLUDE_PO_CHANGED, ['ou' => $newEntity->getOrganisationUnitId(), ($newEntity->isIncludePurchaseOrdersInAvailable() ? 'true' : 'false')], [static::LOG_CODE, 'IncludePoChanged']);
        $workload = new UpdateIncludePurchaseOrderFromDefaultWorkload($newEntity->getOrganisationUnitId(), $newEntity->isIncludePurchaseOrdersInAvailable());
        $this->gearmanClient->doBackground(UpdateIncludePurchaseOrderFromDefaultWorkload::FUNCTION_NAME, serialize($workload));
    }

    protected function handleLowStockThresholdChange(Entity $existingEntity, Entity $newEntity): void
    {
        if ($existingEntity->isLowStockThresholdOn() == $newEntity->isLowStockThresholdOn()
            && $existingEntity->getLowStockThresholdValue() == $newEntity->getLowStockThresholdValue()) {
            return;
        }
        $this->logDebug(static::LOG_INCLUDE_LOW_STOCK_THRESHOLD_CHANGED, ['ou' => $newEntity->getOrganisationUnitId(), ($newEntity->isLowStockThresholdOn() ? 'true' : 'false')], [static::LOG_CODE, 'LowStockThresholdChanged']);
        $this->updateLowStockThresholdFromDefaultGenerator->generateJob($newEntity->getOrganisationUnitId());
    }
}
