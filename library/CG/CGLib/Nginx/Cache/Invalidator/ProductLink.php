<?php
namespace CG\CGLib\Nginx\Cache\Invalidator;

use CG\CGLib\Nginx\Cache\AbstractInvalidator;
use CG\CGLib\Nginx\Cache\Resource;
use CG\Product\LinkRelated\Service as LinkRelatedService;

class ProductLink extends AbstractInvalidator
{
    const TYPE_LEAF = 'leaf';
    const TYPE_NODE = 'node';
    const TYPE_RELATED = 'related';
    const TYPE_PATHS = 'paths';

    /** @var LinkRelatedService */
    protected $linkRelatedService;

    protected $uriTypeMap = [
        self::TYPE_LEAF => '/productLinkLeaf',
        self::TYPE_NODE => '/productLinkNode',
        self::TYPE_RELATED => '/productLinkRelated',
        self::TYPE_PATHS => '/productLinkPaths',
    ];

    public function invalidateRelated($id)
    {
        $this->purgeResources([
            new Resource(static::TYPE_LEAF, $id),
            new Resource(static::TYPE_NODE, $id),
            new Resource(static::TYPE_RELATED, $id),
            new Resource(static::TYPE_PATHS, $id),
        ]);
    }

    protected function getUriForType($type)
    {
        return $this->uriTypeMap[$type];
    }
}