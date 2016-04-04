<?php
namespace CG\Courier;

use CG\Root\Mapper as RootMapper;
use CG\Slim\Renderer\ResponseType\Hal;
use CG\Amazon\Carrier\Storage\Api as Amazon;

class Mapper extends RootMapper
{
    public function getHal()
    {
        return (new Hal('/courier'))
            ->addLink('amazon', Amazon::URI);
    }
} 