<?php
namespace CG\Stock\Location;

class TypedMapper extends Mapper
{
    public function fromArray(array $location)
    {
        $entity = parent::fromArray($location);
        if (($entity instanceof TypedEntity) && isset($location['type'])) {
            $entity->setType($location['type']);
        }
        return $entity;
    }
}