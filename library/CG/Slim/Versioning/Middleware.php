<?php
namespace CG\Slim\Versioning;

use Slim\Middleware as SlimMiddleware;
use Zend\Di\Di;
use Zend\Di\Exception\ClassNotFoundException;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\BadRequest;
use Nocarrier\Hal;
use CG\Slim\Renderer\ResponseType\Hal as HalResponse;

class Middleware extends SlimMiddleware
{
    const VERSION_ROUTE = '/version';
    const VERSION_HEADER = 'Version';

    protected $di;
    protected $halResponse;
    protected $route;
    protected $versions = array();
    protected $version;
    protected $requested;

    public function __construct(Di $di, Slim $app, HalResponse $halResponse)
    {
        $this->setDi($di);
        $this->setApplication($app);
        $this->setHalResponse($halResponse);
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

    public function setHalResponse(HalResponse $halResponse)
    {
        $this->halResponse = $halResponse;
        return $this;
    }

    public function getHalResponse()
    {
        return $this->halResponse;
    }

    public function versionRoute()
    {
        $halResponse = $this->getHalResponse();

        $halData = $halResponse->getData();
        foreach ($this->versions as $routeName => $version) {
            $router = $this->getApplication()->router();
            if (!$router->hasNamedRoute($routeName)) {
                continue;
            }

            $routePattern = $router->getNamedRoute($routeName)->getPattern();

            $halData[$routePattern] = [
                'min' => $version->getMin(),
                'max' => $version->getMax()
            ];
        }
        $halResponse->setData($halData);


        $this->getApplication()->view()->set(
            'RestResponse',
            $halResponse
        );
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
            implode(
                '_',
                ['Versioniser', $this->route->getName(), $currentVersion]
            )
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

        for ($currentVersion = $this->requested; $currentVersion <= $version->getMax(); $currentVersion++) {
            try {
                $versioniser = $this->getVersioniser($currentVersion);
            } catch (ClassNotFoundException $exception) {
                // No Versioniser - Move Along
                continue;
            }

            if (!($versioniser instanceof VersioniserInterface)) {
                continue;
            }

            $upgradedVersion = $versioniser->upgradeRequest($this->route->getParams(), $restRequest);
            if (is_int($upgradedVersion) && $upgradedVersion > $currentVersion) {
                $currentVersion = $upgradedVersion;
            }
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

        for ($currentVersion = $version->getMax(); $currentVersion >= $this->requested; $currentVersion--) {
            try {
                $versioniser = $this->getVersioniser($currentVersion);
            } catch (ClassNotFoundException $exception) {
                // No Versioniser - Move Along
                continue;
            }

            if (!($versioniser instanceof VersioniserInterface)) {
                continue;
            }

            $downgradedVersion = $versioniser->downgradeResponse($this->route->getParams(), $restResponse, $this->requested);
            if (is_int($downgradedVersion) && $downgradedVersion < $currentVersion) {
                $currentVersion = $downgradedVersion;
            }
        }

        $this->getApplication()->view()->set('RestResponse', $restResponse);
    }
}