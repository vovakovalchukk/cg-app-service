<?php
namespace CG\Slim\Versioning\OrderLabelCollection;

use CG\Slim\Versioning\OrderLabelEntity\Versioniser2 as EntityVersioniser2;

class Versioniser2 extends Versioniser1
{
    public function __construct(EntityVersioniser2 $entityVersioner)
    {
        $this->setEntityVersioner($entityVersioner);
    }
}
