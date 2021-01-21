<?php
namespace CG\Slim\Versioning\TrackingCollection;

use CG\Slim\Versioning\TrackingEntity\Versioniser2 as EntityVersioner;

class Versioniser2 extends Versioniser1
{
    public function __construct(EntityVersioner $entityVersioner)
    {
        $this->entityVersioner = $entityVersioner;
    }
}