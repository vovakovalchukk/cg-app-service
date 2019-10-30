<?php
namespace CG\Slim\Versioning\TemplateCollection;

use CG\Slim\Versioning\TemplateEntity\Versioniser3 as EntityVersioner;

class Versioniser3 extends Versioniser1
{
    public function __construct(EntityVersioner $entityVersioner)
    {
        $this->entityVersioner = $entityVersioner;
    }
}