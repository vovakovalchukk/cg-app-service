<?php
namespace CG\Slim\Versioning\InvoiceMappingCollection;

use CG\Slim\Versioning\InvoiceMappingEntity\Versioniser1 as InvoiceMappingEntityVersioner1;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    /**
     * @var InvoiceMappingEntityVersioner1
     */
    protected $entityVersioner;

    public function __construct(InvoiceMappingEntityVersioner1 $entityVersioner)
    {
        $this->setEntityVersioner($entityVersioner);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->entityVersioner->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['invoiceMapping'])) {
            return $this->entityVersioner->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['invoiceMapping'] as $invoiceMapping) {
            $currentVersion = $this->entityVersioner->downgradeResponse($params, $invoiceMapping, $requestedVersion);
        }
        return $currentVersion;
    }

    /**
     * @return self
     */
    protected function setEntityVersioner($entityVersioner)
    {
        $this->entityVersioner = $entityVersioner;
        return $this;
    }
} 
