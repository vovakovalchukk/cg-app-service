<?php

namespace CG\Settings\Shipping\Alias\Nginx\Cache;

use CG\CGLib\Nginx\Cache\AbstractInvalidator;
use CG\CGLib\Nginx\Cache\Resource;
use CG\Settings\Shipping\Alias\Entity as AliasEntity;
use CG\Settings\Shipping\Alias\Mapper as AliasMapper;
use CG\Settings\Shipping\Alias\Rule\Entity as RuleEntity;
use CG\Settings\Shipping\Alias\Rule\Mapper as RuleMapper;

class Invalidator extends AbstractInvalidator
{
    public const TYPE_ALIAS = 'alias';
    public const TYPE_ALIAS_RULE = 'aliasRule';
    protected const URI_TYPE_MAP = [
        self::TYPE_ALIAS => AliasMapper::URL,
        self::TYPE_ALIAS_RULE => RuleMapper::URL,
    ];

    public function invalidateAlias(AliasEntity $entity): void
    {
        $resources = [new Resource(static::TYPE_ALIAS, $entity->getId())];
        $resources += array_map(
            function ($id) {
                return new Resource(static::TYPE_ALIAS_RULE, $id);
            },
            $entity->getRules()->getIds()
        );
        try {
            $this->purgeResources($resources);
        } catch (\Throwable $e) {
            // ignore errors
        }
    }

    public function invalidateAliasForRules(RuleEntity $entity): void
    {
        $resources = [
            new Resource(self::TYPE_ALIAS, $entity->getShippingAliasId()),
            new Resource(self::TYPE_ALIAS_RULE, $entity->getId()),
        ];
        try {
            $this->purgeResources($resources);
        } catch (\Throwable $e) {
            // ignore errors
        }
    }

    protected function getUriForType($type)
    {
        return static::URI_TYPE_MAP[$type];
    }
}