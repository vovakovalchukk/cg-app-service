<?php
namespace CG\Slim\Versioning;

use Slim\Middleware as SlimMiddleware;
use Slim\Slim;

class Middleware extends SlimMiddleware
{
    protected $route;
    protected $versions = array();

    public function __construct(Slim $app)
    {
        $this->setApplication($app);
    }

    public function setRouteVersion(array $request)
    {
        if (!isset($request['version']) || !($request['version'] instanceof Version)) {
            return;
        }
        $this->versions[$request['name']] = $request['version'];
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