<?php
namespace CG\Slim\Versioning\OrderCollection;

use CG\Slim\Versioning\OrderEntity\Versioniser13 as EntityVersioniser;

class Versioniser13 extends Versioniser9
{
    public function __construct(EntityVersioniser $entityVersioner)
    {
        $this->setEntityVersioner($entityVersioner);
    }
} 
