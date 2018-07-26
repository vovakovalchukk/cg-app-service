<?php
namespace CG\Slim\Versioning\OrderLabelCollection;

use CG\Slim\Versioning\OrderLabelEntity\Versioniser5 as EntityVersioniser;

class Versioniser5 extends Versioniser1
{
    public function __construct(EntityVersioniser $entityVersioner)
    {
        $this->setEntityVersioner($entityVersioner);
    }
}
