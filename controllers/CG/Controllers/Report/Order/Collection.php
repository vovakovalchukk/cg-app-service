<?php
namespace CG\Controllers\Report\Order;

use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Order\Service\Filter;
use CG\Permission\Exception as PermissionException;
use CG\Report\Order\Service as ReportOrderService;
use CG\Slim\ControllerTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
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
        try {
            return $this->getService()->fetch(
                $this->getOrderFilter(),
                $dimension,
                $this->getParams('metric') ?: []
            );
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(),$e);
        } catch (PermissionException $e) {
            throw new HttpNotFound('Not found.', $e->getCode(), $e);
        }
    }

    protected function getOrderFilter(): Filter
    {
        return $this->getDi()->newInstance(Filter::class, $this->getParams());
    }
}
