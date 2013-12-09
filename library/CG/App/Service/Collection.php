<?php
namespace CG\App\Service;

use CG\Stdlib\PaginatedCollection;

class Collection extends PaginatedCollection
{
    protected $ids = array();

    public function attach($object, $data = null)
    {
        $this->ids[$object->getId()] = "";
        parent::attach($object, $data);
    }

    public function detach($object)
    {
        unset($this->ids[$object->getId()]);
        parent::attach($object);
    }

    public function getIds()
    {
        return array_keys($this->ids);
    }
}
