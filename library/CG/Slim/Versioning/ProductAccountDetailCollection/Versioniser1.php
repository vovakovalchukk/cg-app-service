<?php
namespace CG\Slim\Versioning\ProductAccountDetailCollection;

use CG\Slim\Versioning\ProductAccountDetailEntity\Versioniser1 as EntityVersioniser;

class Versioniser1 extends AbstractVersioniser
{
    public function __construct(EntityVersioniser $entityVersioniser)
    {
        $this->entityVersioniser = $entityVersioniser;
    }
}