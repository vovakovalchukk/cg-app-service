<?php
namespace CG\Slim\Versioning\OrderItemCollection;

use CG\Slim\Versioning\VersioniserInterface;
use CG\Slim\Versioning\OrderItemEntity\Versioniser9 as EntityVersioniser;
use CG\Slim\Versioning\OrderItemEntity\Versioniser8 as PreviousEntityVersioniser;

class Versioniser9 extends PreviousEntityVersioniser implements VersioniserInterface
{
    public function __construct(EntityVersioniser $entityVersioner)
    {
        $this->setEntityVersioner($entityVersioner);
    }
}