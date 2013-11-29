<?php
namespace CG\Slim\Versioning;

use Nocarrier\Hal;

interface VersioniserInterface
{
    public function upgradeRequest(Hal $request);
    public function downgradeResponse(Hal $response, $requestedVersion);
} 