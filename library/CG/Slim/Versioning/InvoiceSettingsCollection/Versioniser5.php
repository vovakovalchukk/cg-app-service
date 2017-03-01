<?php
namespace CG\Slim\Versioning\InvoiceSettingsCollection;

use CG\Slim\Versioning\InvoiceSettingsEntity\Versioniser5 as InvoiceSettingsEntityVersioniser5;

class Versioniser5 extends Versioniser4
{
    public function __construct(InvoiceSettingsEntityVersioniser5 $entityVersioniser)
    {
        $this->setEntityVersioniser($entityVersioniser);
    }
}
