<?php
namespace CG\Slim\Versioning\TrackingCollection;

use CG\Slim\Versioning\TrackingEntity\Versioniser3 as EntityVersioner;

class Versioniser3 extends Versioniser1
{
    public function __construct(EntityVersioner $entityVersioner)
    {
        $this->entityVersioner = $entityVersioner;
    }
}