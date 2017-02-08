<?php
namespace CG\Order\Service\Item\Image;

use CG\Order\Service\Item\Service;
use CG\Order\Shared\Item\Mapper;
use CG\Slim\Etag\Helper\Put as Puthelper;

class EtagHelper extends Puthelper
{
    protected function getRequestEntity()
    {
        /** @var Mapper $mapper */
        $mapper = $this->getDi()->get($this->getConfig()->getMapperClass());
        return $mapper->fromImageHal(
            $this->fetchCurrentEntity(),
            $this->getApp()->request()->getBody()
        );
    }

    protected function getResponseEntity()
    {
        return $this->fetchCurrentEntity();
    }

    protected function fetchCurrentEntity()
    {
        /** @var Service $service */
        $service =  $this->getDi()->get($this->getConfig()->getServiceClass());
        return $service->fetch($this->getRoute()->getParam('orderItemId'));
    }
}
