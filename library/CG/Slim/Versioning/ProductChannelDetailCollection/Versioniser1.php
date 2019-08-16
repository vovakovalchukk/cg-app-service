<?php
namespace CG\Slim\Versioning\ProductChannelDetailCollection;

use CG\Slim\Versioning\ProductChannelDetailEntity\Versioniser1 as EntityVersioniser;

class Versioniser1 extends AbstractVersioniser
{
    public function __construct(EntityVersioniser $entityVersioniser)
    {
        $this->entityVersioniser = $entityVersioniser;
    }
}