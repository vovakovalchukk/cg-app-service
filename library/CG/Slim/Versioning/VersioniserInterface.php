<?php
namespace CG\Slim\Versioning;

use Nocarrier\Hal;

interface VersioniserInterface
{
    public function upgradeRequest(array $params, Hal $request);
    public function downgradeResponse(array $params, Hal $response, $requestedVersion);
} 