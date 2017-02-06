<?php
namespace CG\Slim\Versioning\OrderLabelCollection;

use CG\Slim\Versioning\OrderLabelEntity\Versioniser4 as EntityVersioniser4;

class Versioniser4 extends Versioniser3
{
    public function __construct(EntityVersioniser4 $entityVersioner)
    {
        $this->setEntityVersioner($entityVersioner);
    }
}
