<?php
namespace CG\CGLib\Nginx\Cache\Invalidator;

use CG\CGLib\Nginx\Cache\AbstractInvalidator;

class ProductGraph extends AbstractInvalidator
{
    const TYPE_GRAPH = 'graph';
    const TYPE_LINK = 'link';

    protected $uriTypeMap = [
        self::TYPE_GRAPH => '/productGraph',
        self::TYPE_LINK => '/productLink',
    ];

    public function invalidate($id)
    {
        $this->purgeResource(static::TYPE_GRAPH, $id);
        $this->purgeResource(static::TYPE_LINK, $id);
    }

    protected function getUriForType($type)
    {
        return $this->uriTypeMap[$type];
    }
}