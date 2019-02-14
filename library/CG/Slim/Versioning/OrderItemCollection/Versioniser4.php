<?php
namespace CG\Slim\Versioning\OrderItemCollection;

use CG\Slim\Versioning\VersioniserInterface;
use CG\Slim\Versioning\OrderItemEntity\Versioniser4 as EntityVersioniser;
use CG\Slim\Versioning\OrderItemEntity\Versioniser3 as PreviousEntityVersioniser;

class Versioniser4 extends PreviousEntityVersioniser implements VersioniserInterface
{
    public function __construct(EntityVersioniser $entityVersioner)
    {
        $this->setEntityVersioner($entityVersioner);
    }
}