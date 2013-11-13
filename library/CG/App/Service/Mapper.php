<?php
namespace Application\Service;

use CG\Stdlib\Mapper\FromArrayInterface;
use Application\Urls\Service as ServiceUrls;
use Application\Mapper\GetHalModelTrait;
use Zend\Di\Di;
use Application\Service\Event\Mapper as EventMapper;
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
    protected $eventMapper;

    public function __construct(Di $di, ArrayValidator $arrayValidator, EventMapper $eventMapper)
    {
        $this->setDi($di);
        $this->setEventMapper($eventMapper);
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

    public function setEventMapper(EventMapper $eventMapper)
    {
        $this->eventMapper = $eventMapper;
    }

    public function getEventMapper()
    {
        return $this->eventMapper;
    }

    public function toHalModel(Entity $entity, ServiceUrls $urls, $addLinks = true)
    {
        $model = $this->getHalModel($urls->getServiceUrl($entity->getId()));
        $model->setVariables(array(
            'id' => $entity->getId(),
            'type' => $entity->getType(),
            'endpoint' => $entity->getEndpoint()
        ));

        if ($addLinks) {
            $model->addLink(
                $this->getHalModel($urls->getServiceListUrl()),
                'all'
            );
        }

        $model->addLink(
            $this->getHalModel($urls->getServiceEventList($entity->getId())),
            'subscribedEvents'
        );

        foreach ($entity->getSubscribedEvents() as $event) {
            $model->addResource(
                $this->getEventMapper()->toHalModel($event, $urls, false),
                'subscribedEvents'
            );
        }

        return $model;
    }

    public function collectionToHalModel(SplObjectStorage $collection, ServiceUrls $urls)
    {
        $model = $this->getHalModel(
            $urls->getServiceListUrl()
        );

        foreach ($collection as $entity) {
            $model->addResource(
                $this->toHalModel($entity, $urls, false),
                'service'
            );
        }

        return $model;
    }

    public function toArray(Entity $entity)
    {
        return array(
            'id' => $entity->getId(),
            'type' => $entity->getType(),
            'endpoint' => $entity->getEndpoint()
        );
    }

    public function fromArray(array $entityData)
    {
        $this->getArrayValidator()->validate($entityData);
        return $this->getDi()->get('Application\Service\Entity', $entityData);
    }

    public function fromHal(Hal $entityHal)
    {
        return $this->fromArray($entityHal->getData());
    }

    public function eventHalFromHal(Hal $entityHal)
    {
        $resources = $entityHal->getResources();
        return isset($resources['subscribedEvents']) ? $resources['subscribedEvents'] : array();
    }
}
