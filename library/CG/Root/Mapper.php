<?php
namespace CG\Root;

use CG\Slim\Renderer\ResponseType\Hal;
use Zend\Di\Di;

class Mapper
{
    /** @var  Di */
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function getHal()
    {
        /** @var Hal $hal */
        $hal =  $this->di->get(Hal::class, array('uri' => '/'));
        return $hal
            ->addLink('category', '/category')
            ->addLink('listing', '/listing')
            ->addLink('location', '/location')
            ->addLink('order', '/order')
            ->addLink('orderItem', '/orderItem')
            ->addLink('orderLabel', '/orderLabel')
            ->addLink('orderLink', '/orderLink')
            ->addLink('product', '/product')
            ->addLink('productDetail', '/productDetail')
            ->addLink('productLink', '/productLink')
            ->addLink('settings', '/settings')
            ->addLink('shippingMethod', '/shippingMethod')
            ->addLink('stock', '/stock')
            ->addLink('stockLocation', '/stockLocation')
            ->addLink('stockLog', '/stockLog')
            ->addLink('template', '/template')
            ->addLink('unimportedListing', '/unimportedListing')
            ->addLink('unimportedListingMarketplace', '/unimportedListingMarketplace')
            ->addLink('userPreference', '/userPreference')
            ->addLink('orderCounts', '/orderCounts')
            ->addLink('tracking', '/tracking')
            ->addLink('courier', '/courier')
            ->addLink('shipmentMetadata', '/shipmentMetadata')
            ->addLink('purchaseOrder', '/purchaseOrder')
            ->addLink('purchaseOrderItem', '/purchaseOrderItem');
    }
}
