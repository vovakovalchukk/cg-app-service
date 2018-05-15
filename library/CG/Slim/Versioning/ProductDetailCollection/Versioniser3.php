<?php
namespace CG\Slim\Versioning\ProductDetailCollection;

use CG\Slim\Versioning\ProductDetailEntity\Versioniser3 as EntityVersioniser;
use CG\Slim\Versioning\VersioniserInterface;

class Versioniser3 extends Versioniser1 implements VersioniserInterface
{
    protected $entityVersioner;

    public function __construct(EntityVersioniser $entityVersioner)
    {
        parent::__construct($entityVersioner);
    }
}