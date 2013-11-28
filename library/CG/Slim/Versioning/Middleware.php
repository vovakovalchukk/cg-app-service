<?php
namespace CG\Slim\Versioning;

use Slim\Middleware as SlimMiddleware;
use Slim\Slim;

class Middleware extends SlimMiddleware
{
    protected $route;

    public function __construct(Slim $app)
    {
        $this->setApplication($app);
    }

    public function __invoke()
    {
        $this->route = $this->getApplication()->router()->getCurrentRoute();
    }

    protected function getRoute()
    {
        return $this->route;
    }

    public function call()
    {
        $this->next->call();
    }
}