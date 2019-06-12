<?php
namespace CG\Slim\Versioning\GiftWrapCollection;

use CG\Slim\Versioning\GiftWrapEntity\Versioniser1 as EntityVersioniser;

class Versioniser1 extends AbstractVersioniser
{
    public function __construct(EntityVersioniser $entityVersioniser)
    {
        $this->entityVersioniser = $entityVersioniser;
    }
}