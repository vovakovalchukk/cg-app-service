<?php
namespace CG\Courier;

use CG\Amazon\ShippingService\Storage\Api as Amazon;
use CG\Root\Mapper as RootMapper;
use CG\Slim\Renderer\ResponseType\Hal;

class Mapper extends RootMapper
{
    public function getHal()
    {
        return (new Hal('/courier'))
            ->addLink('amazon', Amazon::URI);
    }
} 