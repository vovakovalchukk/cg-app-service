<?php
namespace CG\Slim\Versioning\ProductDetailCollection;

use CG\Slim\Versioning\ProductDetailEntity\Versioniser6 as EntityVersioniser;

class Versioniser6 extends Versioniser1
{
    public function __construct(EntityVersioniser $entityVersioner)
    {
        parent::__construct($entityVersioner);
    }
}