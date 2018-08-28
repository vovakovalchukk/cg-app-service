<?php
namespace CG\Product\Detail;

use CG\Order\Client\Gearman\Generator\CalculateOrderWeightForSku as CalculateOrderWeightForSkuGearmanJobGenerator;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Slim\Patch\ServiceTrait as PatchTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\EventManager\GlobalEventManager as EventManager;

class RestService extends Service
{
    use PatchTrait;

    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    /** @var EventManager $eventManager */
    protected $eventManager;
    /** @var CalculateOrderWeightForSkuGearmanJobGenerator */
    protected $calculateOrderWeightForSkuGearmanJobGenerator;

    public function __construct(
        EventManager $eventManager,
        StorageInterface $repository,
        Mapper $mapper,
        CalculateOrderWeightForSkuGearmanJobGenerator $calculateOrderWeightForSkuGearmanJobGenerator
    ) {
        parent::__construct($repository, $mapper);
        $this->eventManager = $eventManager;
        $this->calculateOrderWeightForSkuGearmanJobGenerator = $calculateOrderWeightForSkuGearmanJobGenerator;
    }

    public function fetchCollectionByFilterAsHal(Filter $filter)
    {
        if (!$filter->getLimit()) {
            $filter->setLimit(static::DEFAULT_LIMIT);
        }
        if (!$filter->getPage()) {
            $filter->setPage(static::DEFAULT_PAGE);
        }

        $collection = $this->fetchCollectionByFilter($filter);
        return $this->getMapper()->collectionToHal(
            $collection, "/productDetail", $filter->getLimit(), $filter->getPage(), $filter->toArray()
        );
    }

    /**
     * @param ProductDetail $entity
     */
    public function save($entity)
    {
        try {
            $previousEntity = null;
            /** @var ProductDetail $previousEntity */
            try {
                if (is_null($entity->getId())) {
                    throw new NotFound();
                }
                $previousEntity = $this->fetch($entity->getId());
            } catch (NotFound $exception) {
                //noop
            }
            return parent::save($entity);
        } finally {
            if ($previousEntity === null || $previousEntity->getWeight() !== $entity->getWeight()) {
                $this->calculateOrderWeightForSkuGearmanJobGenerator->generateJobForProductDetail($entity);
            }
        }
    }

    public function remove(ProductDetail $entity)
    {
        try {
            parent::remove($entity);
        } finally {
            $this->calculateOrderWeightForSkuGearmanJobGenerator->generateJobForProductDetail($entity);
        }
    }

    /**
     * Required by PatchTrait
     * @return EventManager
     */
    protected function getEventManager()
    {
        return $this->eventManager;
    }
}
