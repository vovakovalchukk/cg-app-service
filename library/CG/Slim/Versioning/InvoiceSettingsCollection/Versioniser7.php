<?php
namespace CG\Slim\Versioning\InvoiceSettingsCollection;

use CG\Slim\Versioning\InvoiceSettingsEntity\Versioniser7 as InvoiceSettingsEntityVersioniser7;

class Versioniser7 extends Versioniser4
{
    public function __construct(InvoiceSettingsEntityVersioniser7 $entityVersioniser)
    {
        $this->setEntityVersioniser($entityVersioniser);
    }
}
