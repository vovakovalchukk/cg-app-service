<?php
namespace CG\Controllers\Settings;

use CG\Slim\ControllerTrait;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Slim\Renderer\ResponseType\Hal;
use Slim\Slim;
use Zend\Di\Di;

class Clearbooks implements LoggerAwareInterface
{
    use ControllerTrait, LogTrait;

    public function __construct(Slim $app, Di $di)
    {
        $this->setSlim($app)
            ->setDi($di);
    }

    public function get()
    {
        return $this->getDi()->get(Hal::class, ['uri' => '/settings/clearbooks'])
            ->addLink('customer', '/settings/clearbooks/customer');
    }
}
