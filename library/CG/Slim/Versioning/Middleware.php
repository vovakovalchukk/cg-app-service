<?php
namespace CG\Slim\Versioning;

use Slim\Middleware as SlimMiddleware;
use Zend\Di\Di;
use Zend\Di\Exception\ExceptionInterface as DiException;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\BadRequest;
use Nocarrier\Hal;

class Middleware extends SlimMiddleware
{
    const VERSION_HEADER = 'Version';

    protected $di;
    protected $route;
    protected $versions = array();
    protected $version;
    protected $requested;

    public function __construct(Di $di, Slim $app)
    {
        $this->setDi($di);
        $this->setApplication($app);
    }

    public function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }

    public function getDi()
    {
        return $this->di;
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
        $this->versionRequest();
    }

    public function call()
    {
        $this->next->call();
        $this->versionResponse();
    }

    protected function getVersioniser($currentVersion)
    {
        return $this->getDi()->get(
            __NAMESPACE__ . '\\' . $this->route->getName() . '\\Versioniser' . $currentVersion
        );
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

    protected function versionRequest()
    {
        $version = $this->version;
        if (!($version instanceof Version)) {
            return;
        }

        $environment = $this->getApplication()->environment();
        if (!isset($environment['slim.input']) || !($environment['slim.input'] instanceof Hal)) {
            return;
        }
        $restRequest = $environment['slim.input'];

        foreach (range($this->requested, $version->getMax()) as $currentVersion) {
            try {
                $versioniser = $this->getVersioniser($currentVersion);
            } catch (DiException $exception) {
                // No Versioniser - Move Along
                continue;
            }

            if (!($versioniser instanceof VersioniserInterface)) {
                continue;
            }

            $versioniser->upgradeRequest($restRequest);
        }

        $environment['slim.input'] = $restRequest;
    }

    protected function versionResponse()
    {
        $version = $this->version;
        if (!($version instanceof Version)) {
            return;
        }

        $restResponse = $this->getApplication()->view()->get('RestResponse');
        if (!is_object($restResponse) || !$restResponse instanceof Hal) {
            return;
        }

        foreach (range($version->getMax(), $this->requested, -1) as $currentVersion) {
            try {
                $versioniser = $this->getVersioniser($currentVersion);
            } catch (DiException $exception) {
                // No Versioniser - Move Along
                continue;
            }

            if (!($versioniser instanceof VersioniserInterface)) {
                continue;
            }

            $versioniser->downgradeResponse($restResponse);
        }

        $this->getApplication()->view()->set('RestResponse', $restResponse);
    }
}