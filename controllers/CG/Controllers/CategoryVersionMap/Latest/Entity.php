<?php
namespace CG\Controllers\CategoryVersionMap\Latest;

use CG\Product\Category\VersionMap\Service;
use CG\Slim\Controller\Entity\DeleteTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;

class Entity
{
    use ControllerTrait;
    use PutTrait;
    use DeleteTrait;

    /** @var Slim $slim */
    protected $slim;
    /** @var Service $service */
    protected $service;

    public function __construct(Slim $slim, Service $service)
    {
        $this->slim = $slim;
        $this->service = $service;
    }

    public function get()
    {
        try {
            return $this->getService()->fetchLatestAsHal();
        } catch(NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        } catch (PermissionException $e) {
            throw new HttpNotFound('Entity Not Found', $e->getCode(), $e);
        }
    }

    protected function getSlim()
    {
        return $this->slim;
    }

    protected function getService()
    {
        return $this->service;
    }
}