<?php
namespace CG\Slim\Versioning\ProductDetailCollection;

use CG\Slim\Versioning\ProductDetailEntity\Versioniser5 as EntityVersioniser;

class Versioniser5 extends Versioniser1
{
    public function __construct(EntityVersioniser $entityVersioner)
    {
        parent::__construct($entityVersioner);
    }
}