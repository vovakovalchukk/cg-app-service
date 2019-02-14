<?php
namespace CG\Slim\Versioning\OrderItemCollection;

use CG\Slim\Versioning\VersioniserInterface;
use CG\Slim\Versioning\OrderItemEntity\Versioniser3 as EntityVersioniser;
use CG\Slim\Versioning\OrderItemEntity\Versioniser2 as PreviousEntityVersioniser;

class Versioniser3 extends PreviousEntityVersioniser implements VersioniserInterface
{
    public function __construct(EntityVersioniser $entityVersioner)
    {
        $this->setEntityVersioner($entityVersioner);
    }
}