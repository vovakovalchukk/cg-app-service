<?php
namespace CG\Slim\Versioning\OrderItemEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    public function __construct()
    {
    }

    public function upgradeRequest(array $params, Hal $request)
    {

    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {

    }
}