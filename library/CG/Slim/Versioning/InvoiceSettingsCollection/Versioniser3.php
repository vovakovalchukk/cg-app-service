<?php
namespace CG\Slim\Versioning\InvoiceSettingsCollection;

use CG\Slim\Versioning\InvoiceSettingsEntity\Versioniser3 as InvoiceSettingsEntityVersioniser3;

class Versioniser3 extends Versioniser3
{
    public function __construct(InvoiceSettingsEntityVersioniser3 $entityVersioniser)
    {
        $this->setEntityVersioniser($entityVersioniser);
    }
}
