<?php
namespace CG\Slim\Versioning\ProductDetailCollection;

use CG\Slim\Versioning\ProductDetailEntity\Versioniser4 as EntityVersioniser;

class Versioniser4 extends Versioniser1
{
    public function __construct(EntityVersioniser $entityVersioner)
    {
        parent::__construct($entityVersioner);
    }
}