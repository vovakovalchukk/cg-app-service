<?php
namespace CG\Slim\Versioning\OrderLabelCollection;

use CG\Slim\Versioning\OrderLabelEntity\Versioniser6 as EntityVersioniser;

class Versioniser6 extends Versioniser1
{
    public function __construct(EntityVersioniser $entityVersioner)
    {
        $this->setEntityVersioner($entityVersioner);
    }
}
