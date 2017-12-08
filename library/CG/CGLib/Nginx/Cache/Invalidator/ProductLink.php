<?php
namespace CG\CGLib\Nginx\Cache\Invalidator;

use CG\CGLib\Nginx\Cache\AbstractInvalidator;

class ProductLink extends AbstractInvalidator
{
    const TYPE_LEAF = 'leaf';
    const TYPE_NODE = 'node';

    protected $uriTypeMap = [
        self::TYPE_LEAF => '/productLinkLeaf',
        self::TYPE_NODE => '/productLinkNode',
    ];

    public function invalidateRelated($id)
    {
        $this->purgeResource(static::TYPE_LEAF, $id);
        $this->purgeResource(static::TYPE_NODE, $id);
    }

    protected function getUriForType($type)
    {
        return $this->uriTypeMap[$type];
    }
}