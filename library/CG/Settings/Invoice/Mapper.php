<?php
namespace CG\Settings\Invoice;

use CG\Stdlib\Mapper\FromArrayInterface;
use CG\Stdlib\Storage\MongoDb\MongoArrayTrait;
use CG\Slim\Mapper\CollectionToHalTrait;
use CG\Slim\Renderer\ResponseType\Hal;
use Nocarrier\Hal as NocarrierHal;
use Zend\Di\Di;

class Mapper implements FromArrayInterface
{
    const FIRST_PAGE = 1;
    const API_VERSION = 1;

    use CollectionToHalTrait;
    use MongoArrayTrait;

    public function __construct(Di $di)
    {
        $this->setDi($di);
    }

    public function fromHal(NocarrierHal $hal)
    {
        $array = $hal->getData();
        return $this->fromArray($array);
    }

    public function fromArray(array $invoiceSettings)
    {
        return $this->getDi()->newInstance(Entity::class, $invoiceSettings);
    }

    public function toHal(Entity $entity)
    {
        $hal =  $this->getDi()->newInstance(
            Hal::class,
            array (
                'uri' => '/settings/invoice/' . $entity->getId(),
                'data' => $entity->toArray()
            )
        );
        return $hal;
    }

    public function getEmbeddedResource()
    {
        return "invoiceSettings";
    }

    public function getFirstPage()
    {
        return static::FIRST_PAGE;
    }

    public function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }

    public function getDi()
    {
        return $this->di;
    }
}