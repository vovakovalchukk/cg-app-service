<?php
namespace CG\Controllers\ProductLinkRelated;

use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Http\StatusCode;
use CG\Permission\Exception as PermissionException;
use CG\Product\LinkRelated\Service;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\ControllerTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Slim\Slim;

class Entity implements LoggerAwareInterface
{

    use LogTrait;
    use ControllerTrait;
    use GetTrait;

    /** @var Slim $slim */
    protected $slim;
    /** @var Service $service */
    protected $service;

    public function __construct(Slim $slim, Service $service)
    {

        $this->slim = $slim;
        $this->service = $service;

        $this->logDebug(__METHOD__, [], 'MYTEST');
    }

    /**
     * @return Slim
     */
    protected function getSlim()
    {
        return $this->slim;
    }

    /**
     * @return Service
     */
    protected function getService()
    {
        return $this->service;
    }
}