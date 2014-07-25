<?php
namespace CG\Root;

use CG\Slim\Renderer\ResponseType\Hal;
use Zend\Di\Di;

class Mapper
{
    protected $di;

    public function __construct(Di $di)
    {
        $this->setDi($di);
    }

    public function getHal()
    {
        return $this->getDi()->get(Hal::class, array('uri' => '/'))
                             ->addLink('order', '/order')
                             ->addLink('product', '/product')
                             ->addLink('shippingMethod', '/shippingMethod')
                             ->addLink('userPreference', '/userPreference')
                             ->addLink('settings', '/settings')
                             ->addLink('template', '/template');

    }

    public function setDi(Di $di)
    {
        $this->di = $di;
    }

    public function getDi()
    {
        return $this->di;
    }
}