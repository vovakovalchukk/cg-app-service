<?php
namespace CG\Slim\Versioning\TemplateCollection;

use CG\Slim\Versioning\TemplateEntity\Versioniser2 as EntityVersioner;

class Versioniser2 extends Versioniser1
{
    public function __construct(EntityVersioner $entityVersioner)
    {
        $this->entityVersioner = $entityVersioner;
    }
}