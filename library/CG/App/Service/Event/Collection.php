<?php
namespace CG\App\Service\Event;

use CG\Stdlib\Collection as StdlibCollection;

class Collection extends StdlibCollection
{
    public function toArray()
    {
        $collection = array();
        foreach ($this as $entity)
        {
            $collection[] = $entity->toArray();
        }
        return $collection;
    }
}
