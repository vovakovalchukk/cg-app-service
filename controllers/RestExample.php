<?php
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\Exception\ExceptionInterface;
use Slim\Slim;
use Slim\View;
use Zend\Db\Adapter\Adapter;
use Nocarrier\Hal;

class RestExample
{
    protected $slim;

    public function __construct(Slim $app)
    {
        $this->setSlim($app);
    }

    public function get()
    {
        $hal = new Hal(
            'test',
            array(
                'example_rest_endpoint' => 'true'
            )
        );
        $hal->addLink('self','/rest')->addLink('index page','/');

        return $hal;
    }

    public function setSlim(Slim $slim)
    {
        $this->slim = $slim;
        return $this;
    }

    public function getSlim()
    {
        return $this->slim;
    }
}