<?php
namespace CG\Controllers\Settings\Invoice;

use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Slim\ControllerTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Settings\Invoice\Shared\Filter;
use CG\Settings\Invoice\Service\Service;
use Slim\Slim;

class Collection
{
    use ControllerTrait;

    public function __construct(Slim $app, Service $service)
    {
        $this->setSlim($app)
             ->setService($service);
    }

    public function get()
    {
        try {
            return $this->getService()->fetchCollectionByFilter(
                new Filter(
                    $this->getParams('limit'),
                    $this->getParams('page'),
                    $this->getParams('emailSendAs'),
                    $this->getParams('emailVerified'),
                    $this->getParams('emailBcc'),
                    $this->getParams('copyRequired'),
                    $this->getParams('pendingVerification'),
                    $this->getParams('verifiedEmail'),
                    $this->getParams('id') ?? []
                )
            );
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}
