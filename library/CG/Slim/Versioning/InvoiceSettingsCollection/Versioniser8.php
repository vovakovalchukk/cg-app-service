<?php
namespace CG\Slim\Versioning\InvoiceSettingsCollection;

use CG\Slim\Versioning\InvoiceSettingsEntity\Versioniser8 as InvoiceSettingsEntityVersioniser8;

class Versioniser8 extends Versioniser4
{
    public function __construct(InvoiceSettingsEntityVersioniser8 $entityVersioniser)
    {
        $this->setEntityVersioniser($entityVersioniser);
    }
}
