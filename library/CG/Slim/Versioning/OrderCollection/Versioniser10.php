<?php
namespace CG\Slim\Versioning\OrderCollection;

use CG\Slim\Versioning\OrderEntity\Versioniser10 as EntityVersioniser;

class Versioniser10 extends Versioniser9
{
    public function __construct(EntityVersioniser $entityVersioner)
    {
        $this->setEntityVersioner($entityVersioner);
    }
} 
