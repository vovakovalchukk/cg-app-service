<?php
namespace CG\Slim\Versioning\OrderLabelCollection;

use CG\Slim\Versioning\OrderLabelEntity\Versioniser3 as EntityVersioniser3;

class Versioniser3 extends Versioniser2
{
    public function __construct(EntityVersioniser3 $entityVersioner)
    {
        $this->setEntityVersioner($entityVersioner);
    }
}
