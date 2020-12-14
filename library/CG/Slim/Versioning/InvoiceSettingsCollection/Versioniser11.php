<?php
namespace CG\Slim\Versioning\InvoiceSettingsCollection;

use CG\Slim\Versioning\InvoiceSettingsEntity\Versioniser11 as InvoiceSettingsEntityVersioniser;

class Versioniser11 extends Versioniser4
{
    public function __construct(InvoiceSettingsEntityVersioniser $entityVersioniser)
    {
        $this->setEntityVersioniser($entityVersioniser);
    }
}
