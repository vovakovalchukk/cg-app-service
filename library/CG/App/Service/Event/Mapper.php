<?php
namespace Application\Service\Event;

use CG\Stdlib\Mapper\FromArrayInterface;
use Application\Mapper\GetHalModelTrait;
use Zend\Di\Di;
use Application\Urls\Service as ServiceUrls;
use Application\Controller\ServiceEntityController;
use Application\Controller\ServiceEventEntityController;
use SplObjectStorage;
use Nocarrier\Hal;
use Application\Validator\ArrayData as ArrayValidator;

class Mapper implements FromArrayInterface
{
    use GetHalModelTrait;

    protected $di;
    protected $arrayValidator;

    public function __construct(Di $di, ArrayValidator $arrayValidator)
    {
        $this->setDi($di);
    }

    public function setDi(Di $di)
    {
        $this->di = $di;
    }

    public function getDi()
    {
        return $this->di;
    }

    public function setArrayValidator(ArrayValidator $arrayValidator)
    {
        $this->arrayValidator = $arrayValidator;
    }

    public function getArrayValidator()
    {
        return $this->arrayValidator;
    }

    public function toHalModel(Entity $entity, ServiceUrls $urls, $addLinks = true)
    {
        $model = $this->getHalModel($urls->getServiceEventUrl($entity->getServiceId(), $entity->getType()));

        $model->setVariables(array(
            'type' => $entity->getType(),
            'instances' => $entity->getInstances(),
            'endpoint' => $entity->getEndpoint()
        ));

        if ($addLinks) {
            $model->addLink(
                $this->getHalModel($urls->getServiceEventList($entity->getServiceId())),
                'all'
            );

            $model->addLink(
                $this->getHalModel($urls->getServiceUrl($entity->getServiceId())),
                'service'
            );
        }

        return $model;
    }

    public function collectionToHalModel($serviceId, SplObjectStorage $collection, ServiceUrls $urls)
    {
        $model = $this->getHalModel($urls->getServiceEventList($serviceId));

        $model->addLink(
            $this->getHalModel($urls->getServiceUrl($serviceId)),
            'service'
        );

        foreach ($collection as $entity) {
            $model->addResource(
                $this->toHalModel($entity, $urls, false),
                'subscribedEvents'
            );
        }

        return $model;
    }

    public function toArray(Entity $entity)
    {
        return array(
            'id' => $entity->getId(),
            'service_id' => $entity->getServiceId(),
            'type' => $entity->getType(),
            'instances' => $entity->getInstances(),
            'endpoint' => $entity->getEndpoint()
        );
    }

    public function fromArray(array $entityData)
    {
        $this->getArrayValidator()->validate($entityData);
        return $this->getDi()->get('Application\Service\Event\Entity', $entityData);
    }

    public function fromHal($serviceId, Hal $entityHal)
    {
        return $this->fromArray(array_merge(
            array(
                'serviceId' => $serviceId
            ),
            $entityHal->getData()
        ));
    }
}
