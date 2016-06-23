<?php
namespace CG\Slim\Versioning\OrderCollection;

use CG\Slim\Versioning\OrderEntity\Versioniser8 as EntityVersioniser8;

class Versioniser8 extends Versioniser7
{
    public function __construct(EntityVersioniser8 $entityVersioner)
    {
        $this->setEntityVersioner($entityVersioner);
    }
} 
