<?php
namespace CG\Slim\Versioning\InvoiceSettingsCollection;

use CG\Slim\Versioning\InvoiceSettingsEntity\Versioniser4 as InvoiceSettingsEntityVersioniser4;

class Versioniser4 extends Versioniser3
{
    public function __construct(InvoiceSettingsEntityVersioniser4 $entityVersioniser)
    {
        $this->setEntityVersioniser($entityVersioniser);
    }
}
