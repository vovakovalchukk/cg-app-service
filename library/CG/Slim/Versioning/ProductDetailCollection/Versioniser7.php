<?php
namespace CG\Slim\Versioning\ProductDetailCollection;

use CG\Slim\Versioning\ProductDetailEntity\Versioniser7 as EntityVersioniser;

class Versioniser7 extends Versioniser1
{
    public function __construct(EntityVersioniser $entityVersioner)
    {
        parent::__construct($entityVersioner);
    }
}