<?php
namespace CG\Controllers;

use CG\Slim\ControllerTrait;
use CG\Slim\Renderer\ResponseType\Hal;
use Slim\Slim;
use Zend\Di\Di;

class Settings
{
    use ControllerTrait;

    public function __construct(Slim $app, Di $di)
    {
        $this->setSlim($app)
            ->setDi($di);
    }

    public function get()
    {
        $hal = $this->getDi()
            ->get(Hal::class, ['uri' => '/settings'])
            ->addLink('invoice', '/settings/invoice')
            ->addLink('shipping', '/settings/shipping')
            ->addLink('pickList', '/settings/pickList')
            ->addLink('api', '/settings/api')
            ->addLink('product', '/settings/product')
            ->addLink('setupProgress', '/settings/setupProgress')
            ->addLink('order', '/settings/order')
            ->addLink('vat', '/settings/vat');
        return $hal;
    }
}
