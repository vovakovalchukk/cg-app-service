<?php
namespace Application\Service;

use Application\Urls\Service as ServiceUrls;
use Zend\Mvc\Controller\Plugin\Url;

class Urls implements ServiceUrls
{
    protected $url;
    protected $serviceCollectionRoute;
    protected $serviceEntityRoute;
    protected $serviceEventCollectionRoute;
    protected $serviceEventEntityRoute;
    protected $serviceIdParam;
    protected $eventTypeParam;

    public function __construct(Url $url, $serviceCollectionRoute, $serviceEntityRoute, $serviceEventCollectionRoute, $serviceEventEntityRoute, $serviceIdParam, $eventTypeParam)
    {
        $this->setUrl($url);
        $this->setServiceIdParam($serviceIdParam);
        $this->setServiceCollectionRoute($serviceCollectionRoute);
        $this->setServiceEntityRoute($serviceEntityRoute);
        $this->setServiceEventCollectionRoute($serviceEventCollectionRoute);
        $this->setServiceEventEntityRoute($serviceEventEntityRoute);
        $this->setEventTypeParam($eventTypeParam);
    }

    public function setUrl(Url $url)
    {
        $this->url = $url;
        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setServiceCollectionRoute($serviceCollectionRoute)
    {
        $this->serviceCollectionRoute = $serviceCollectionRoute;
        return $this;
    }

    public function getServiceCollectionRoute()
    {
        return $this->serviceCollectionRoute;
    }

    public function setServiceEntityRoute($serviceEntityRoute)
    {
        $this->serviceEntityRoute = $serviceEntityRoute;
        return $this;
    }

    public function getServiceEntityRoute()
    {
        return $this->serviceEntityRoute;
    }

    public function setServiceEventCollectionRoute($serviceEventCollectionRoute)
    {
        $this->serviceEventCollectionRoute = $serviceEventCollectionRoute;
        return $this;
    }

    public function getServiceEventCollectionRoute()
    {
        return $this->serviceEventCollectionRoute;
    }

    public function setServiceEventEntityRoute($serviceEventEntityRoute)
    {
        $this->serviceEventEntityRoute = $serviceEventEntityRoute;
        return $this;
    }

    public function getServiceEventEntityRoute()
    {
        return $this->serviceEventEntityRoute;
    }

    public function setServiceIdParam($serviceIdParam)
    {
        $this->serviceIdParam = $serviceIdParam;
        return $this;
    }

    public function getServiceIdParam()
    {
        return $this->serviceIdParam;
    }

    public function setEventTypeParam($eventTypeParam)
    {
        $this->eventTypeParam = $eventTypeParam;
        return $this;
    }

    public function getEventTypeParam()
    {
        return $this->eventTypeParam;
    }

    public function getServiceListUrl()
    {
        return $this->getUrl()->fromRoute(
            $this->getServiceCollectionRoute()
        );
    }

    public function getServiceUrl($serviceId)
    {
        return $this->getUrl()->fromRoute(
            $this->getServiceEntityRoute(),
            array(
                $this->getServiceIdParam() => $serviceId
            )
        );
    }

    public function getServiceEventList($serviceId)
    {
        return $this->getUrl()->fromRoute(
            $this->getServiceEventCollectionRoute(),
            array(
                $this->getServiceIdParam() => $serviceId
            )
        );
    }

    public function getServiceEventUrl($serviceId, $eventType)
    {
        return $this->getUrl()->fromRoute(
            $this->getServiceEventEntityRoute(),
            array(
                $this->getServiceIdParam() => $serviceId,
                $this->getEventTypeParam() => $eventType
            )
        );
    }
}
