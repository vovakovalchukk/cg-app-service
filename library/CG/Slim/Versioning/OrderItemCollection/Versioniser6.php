<?php
namespace CG\Slim\Versioning\OrderItemCollection;

use CG\Slim\Versioning\VersioniserInterface;
use CG\Slim\Versioning\OrderItemEntity\Versioniser6 as EntityVersioniser;
use CG\Slim\Versioning\OrderItemEntity\Versioniser5 as PreviousEntityVersioniser;

class Versioniser6 extends PreviousEntityVersioniser implements VersioniserInterface
{
    public function __construct(EntityVersioniser $entityVersioner)
    {
        $this->setEntityVersioner($entityVersioner);
    }
}