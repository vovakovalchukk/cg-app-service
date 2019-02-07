<?php
namespace CG\Slim\Versioning\InvoiceSettingsCollection;

use CG\Slim\Versioning\InvoiceSettingsEntity\Versioniser10 as InvoiceSettingsEntityVersioniser;

class Versioniser10 extends Versioniser4
{
    public function __construct(InvoiceSettingsEntityVersioniser $entityVersioniser)
    {
        $this->setEntityVersioniser($entityVersioniser);
    }
}
