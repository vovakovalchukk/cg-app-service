<?php
namespace CG\Slim\Versioning\StockCollection;

use CG\Slim\Versioning\StockEntity\Versioniser2 as EntityVersioniser;

class Versioniser2 extends Versioniser
{
    public function __construct(EntityVersioniser $entityVersioner)
    {
        $this->entityVersioner = $entityVersioner;
    }
}
