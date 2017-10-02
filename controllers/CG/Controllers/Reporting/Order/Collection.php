<?php
namespace CG\Controllers\Reporting\Order;

use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Order\Service\Filter as OrderFilter;
use CG\Permission\Exception as PermissionException;
use CG\Reporting\Order\Filter;
use CG\Reporting\Order\Service as ReportingOrderService;
use CG\Slim\ControllerTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use Slim\Slim;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait;

    public function __construct(Slim $app, Di $di, ReportingOrderService $service)
    {
        $this->setSlim($app);
        $this->setDi($di);
        $this->setService($service);
    }

    public function get(string $dimension)
    {
        try {
            return $this->getService()->fetch($this->getFilter($dimension));
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(),$e);
        } catch (PermissionException $e) {
            throw new HttpNotFound('Not found.', $e->getCode(), $e);
        }
    }

    protected function getFilter(string $dimension)
    {
        return new Filter(
            $this->getOrderFilter(),
            $dimension,
            $this->getParams('metric') ?: []
        );
    }

    protected function getOrderFilter(): OrderFilter
    {
        return $this->getDi()->newInstance(OrderFilter::class, $this->getParams());
    }
}
