<?php
namespace CG\Controllers;

use CG\Http\StatusCode;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;
use CG\Slim\Renderer\ResponseType\Hal;

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
        $hal = $this->getDi()->get(Hal::class, ['uri' => '/settings'])
                             ->addLink('invoice', '/settings/invoice');
        return $hal;
    }
}
