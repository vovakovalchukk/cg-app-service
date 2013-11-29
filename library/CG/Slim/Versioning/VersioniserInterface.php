<?php
namespace CG\Slim\Versioning;

use CG\Slim\Renderer\ResponseType\Hal;

interface VersioniserInterface
{
    public function upgradeRequest(Hal $request);
    public function downgradeResponse(Hal $response);
} 