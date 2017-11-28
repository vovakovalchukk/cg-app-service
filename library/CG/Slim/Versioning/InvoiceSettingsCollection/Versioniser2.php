<?php
namespace CG\Slim\Versioning\InvoiceSettingsCollection;

use CG\Slim\Versioning\InvoiceSettingsEntity\Versioniser2 as InvoiceSettingsEntityVersioner2;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser2 implements VersioniserInterface
{
    /**
     * @var InvoiceSettingsEntityVersioner2
     */
    protected $entityVersioner;

    public function __construct(InvoiceSettingsEntityVersioner2 $entityVersioner)
    {
        $this->setEntityVersioniser($entityVersioner);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->entityVersioner->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['invoiceSettings'])) {
            return $this->entityVersioner->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['invoiceSettings'] as $invoiceSetting) {
            $currentVersion = $this->entityVersioner->downgradeResponse($params, $invoiceSetting, $requestedVersion);
        }
        return $currentVersion;
    }

    /**
     * @return self
     */
    protected function setEntityVersioniser($entityVersioner)
    {
        $this->entityVersioner = $entityVersioner;
        return $this;
    }
} 
