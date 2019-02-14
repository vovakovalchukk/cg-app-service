<?php
namespace CG\Slim\Versioning\OrderItemCollection;

use CG\Slim\Versioning\VersioniserInterface;
use CG\Slim\Versioning\OrderItemEntity\Versioniser6 as EntityVersioniser;
use CG\Slim\Versioning\OrderItemCollection\Versioniser1 as Versioniser1;

class Versioniser6 extends Versioniser1 implements VersioniserInterface
{
    public function __construct(EntityVersioniser $entityVersioner)
    {
        $this->setEntityVersioner($entityVersioner);
    }
}