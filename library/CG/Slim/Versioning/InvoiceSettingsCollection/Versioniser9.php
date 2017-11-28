<?php
namespace CG\Slim\Versioning\InvoiceSettingsCollection;

use CG\Slim\Versioning\InvoiceSettingsEntity\Versioniser9 as InvoiceSettingsEntityVersioniser9;

class Versioniser9 extends Versioniser4
{
    public function __construct(InvoiceSettingsEntityVersioniser9 $entityVersioniser)
    {
        $this->setEntityVersioniser($entityVersioniser);
    }
}
