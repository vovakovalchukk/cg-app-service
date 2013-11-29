<?php
namespace CG\Slim\Versioning;

use Slim\Middleware as SlimMiddleware;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\BadRequest;

class Middleware extends SlimMiddleware
{
    const VERSION_HEADER = 'Version';

    protected $route;
    protected $versions = array();
    protected $version;
    protected $requested;

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
        $this->parseRequest();
    }

    public function call()
    {
        $this->next->call();
        $this->versionResource();
    }

    protected function parseRequest()
    {
        $route = $this->route;
        if (!$route || !isset($this->versions[$route->getName()])) {
            return;
        }
        $headers = $this->getApplication()->request()->headers;

        $this->version = $this->versions[$route->getName()];
        $this->requested =
            isset($headers[static::VERSION_HEADER])
                ? $headers[static::VERSION_HEADER]
                : $this->version->getMin();

        if (!$this->version->allowedVersion($this->requested)) {
            throw new BadRequest(
                'Unsupported Version Requested: ' . $this->requested
                . ' [' . $this->version->getMin() . '-' . $this->version->getMax() . ']'
            );
        }
    }

    protected function versionResource()
    {
        $version = $this->version;
        if (!($version instanceof Version)) {
            return;
        }

        foreach (range($this->requested, $version->getMax(), -1) as $currentVersion) {

        }
    }
}