<?php
namespace CG\Controllers\Report\Order;

use CG\Order\Service\Filter;
use CG\Order\Service\Storage\Persistent\Db;
use CG\Report\Order\Service as ReportOrderService;
use CG\Report\Order\Service;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait;

    public function __construct(Slim $app, Di $di, ReportOrderService $service)
    {
        $this->setSlim($app);
        $this->setDi($di);
        $this->setService($service);
    }

    public function get(string $dimension)
    {
        /** @var Db $orderDb */
        $filter = $this->getOrderFilter();
        $metrics = $this->getSlim()->request->get('metric', []);
        /** @var Service $service */
        $service = $this->getService();
        $service->fetch($filter, $dimension, $metrics);
    }

    protected function getOrderFilter(): Filter
    {
        return $this->getDi()->newInstance(Filter::class, $this->getParams());
    }
}
