<?php
namespace CG\Controllers\Ekm;

use CG\Slim\ControllerTrait;
use CG\Slim\Renderer\ResponseType\Hal;
use Slim\Slim;
use Zend\Di\Di;

class Ekm
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
            ->get(Hal::class, ['uri' => '/ekm'])
            ->addLink('registration', '/ekm/registration');
        return $hal;
    }
}
