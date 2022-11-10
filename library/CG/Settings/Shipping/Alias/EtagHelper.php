<?php

namespace CG\Settings\Shipping\Alias;

use CG\Settings\Shipping\Alias\Service as AliasService;
use CG\Settings\Shipping\Alias\Mapper as AliasMapper;
use CG\Slim\Etag\Helper\Put as PutHelper;

class EtagHelper extends PutHelper
{
    protected function getRequestEntity(): Entity
    {
        /** @var AliasMapper $mapper */
        $mapper = $this->getDi()->get($this->getConfig()->getMapperClass());

        return $mapper->fromRuleHal(
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
        /** @var AliasService $service */
        $service = $this->getDi()->get($this->getConfig()->getServiceClass());

        return $service->fetch($this->getRoute()->getParam('aliasId'));
    }
}