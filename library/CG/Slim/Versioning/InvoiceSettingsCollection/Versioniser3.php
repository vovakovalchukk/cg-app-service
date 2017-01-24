<?php
namespace CG\Slim\Versioning\InvoiceSettingsCollection;

use CG\Slim\Versioning\InvoiceSettingsEntity\Versioniser3 as InvoiceSettingsEntityVersioniser3;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser3 implements VersioniserInterface
{
    /**
     * @var InvoiceSettingsEntityVersioniser3
     */
    protected $entityVersioniser;

    public function __construct(InvoiceSettingsEntityVersioniser3 $entityVersioniser)
    {
        $this->setEntityVersioniser($entityVersioniser);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->entityVersioniser->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['invoiceSettings'])) {
            return $this->entityVersioniser->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['invoiceSettings'] as $invoiceSetting) {
            $currentVersion = $this->entityVersioniser->downgradeResponse($params, $invoiceSetting, $requestedVersion);
        }
        return $currentVersion;
    }

    /**
     * @return self
     */
    protected function setEntityVersioniser($entityVersioniser)
    {
        $this->entityVersioniser = $entityVersioniser;
        return $this;
    }
}
