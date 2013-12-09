<?php
namespace CG\App\Service\Event;

use CG\Stdlib\PaginatedCollection;
use CG\Stdlib\Collection\ToArrayTrait;

class Collection extends PaginatedCollection
{
    use ToArrayTrait;

    protected $entitiesByServiceId;

    public function getByServiceId($serviceId)
    {
        $collection = new static($this->getEntityClass(), $this->getSourceDescription(), array("serviceId" => $serviceId));
        if (isset($this->getEntitiesByServiceId()[$serviceId])) {
            foreach ($this->getEntitiesByServiceId()[$serviceId] as $entity)
            {
                $collection->attach($entity);
            }
        }
        return $collection;
    }

    public function attach($object, $data = null)
    {
        $this->entitiesByServiceId[$object->getServiceId()][] = $object;
        parent::attach($object, $data);
    }

    public function detach($object)
    {
        unset($this->entitiesByServiceId[$object->getServiceId()]);
        parent::detach($object);
    }

    protected function getEntitiesByServiceId()
    {
        return $this->entitiesByServiceId;
    }
}
