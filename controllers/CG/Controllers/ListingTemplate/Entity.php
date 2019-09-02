<?php
namespace CG\Controllers\ListingTemplate;

use CG\Listing\Template\RestService;
use CG\Slim\Controller\Entity\DeleteTrait;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;

class Entity
{
    use ControllerTrait;
    use GetTrait;
    use PutTrait;
    use DeleteTrait;

    /** @var Slim $slim */
    protected $slim;
    /** @var RestService $service */
    protected $service;

    public function __construct(Slim $slim, RestService $service)
    {
        $this->slim = $slim;
        $this->service = $service;
    }
}