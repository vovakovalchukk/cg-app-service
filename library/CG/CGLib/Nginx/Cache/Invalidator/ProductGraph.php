<?php
namespace CG\CGLib\Nginx\Cache\Invalidator;

use CG\CGLib\Nginx\Cache\AbstractInvalidator;

class ProductGraph extends AbstractInvalidator
{
    const TYPE_GRAPH = 'graph';
    protected $uriTypeMap = [
        self::TYPE_GRAPH => '/productGraph',
    ];

    public function invalidate($id)
    {
        $this->purgeResource(static::TYPE_GRAPH, $id);
    }

    protected function getUriForType($type)
    {
        return $this->uriTypeMap[$type];
    }
}