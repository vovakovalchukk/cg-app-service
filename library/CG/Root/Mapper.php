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
                             ->addLink('listing', '/listing')
                             ->addLink('location', '/location')
                             ->addLink('order', '/order')
                             ->addLink('product', '/product')
                             ->addLink('settings', '/settings')
                             ->addLink('shippingMethod', '/shippingMethod')
                             ->addLink('stock', '/stock')
                             ->addLink('stockLocation', '/stockLocation')
                             ->addLink('template', '/template')
                             ->addLink('unimportedListing', '/unimportedListing')
                             ->addLink('userPreference', '/userPreference');
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