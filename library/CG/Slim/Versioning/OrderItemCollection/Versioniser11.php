<?php
namespace CG\Slim\Versioning\OrderItemCollection;

use CG\Slim\Versioning\VersioniserInterface;
use CG\Slim\Versioning\OrderItemEntity\Versioniser11 as EntityVersioniser;
use CG\Slim\Versioning\OrderItemCollection\Versioniser1 as Versioniser1;

class Versioniser11 extends Versioniser1 implements VersioniserInterface
{
    public function __construct(EntityVersioniser $entityVersioner)
    {
        $this->setEntityVersioner($entityVersioner);
    }
}