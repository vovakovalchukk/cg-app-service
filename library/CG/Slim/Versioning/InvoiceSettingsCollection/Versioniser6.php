<?php
namespace CG\Slim\Versioning\InvoiceSettingsCollection;

use CG\Slim\Versioning\InvoiceSettingsEntity\Versioniser6 as InvoiceSettingsEntityVersioniser6;

class Versioniser6 extends Versioniser4
{
    public function __construct(InvoiceSettingsEntityVersioniser6 $entityVersioniser)
    {
        $this->setEntityVersioniser($entityVersioniser);
    }
}
