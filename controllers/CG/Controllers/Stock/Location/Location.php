<?php
namespace CG\Controllers\Stock\Location;

use CG\Permission\Exception as PermissionException;
use CG\Slim\Controller\Entity\DeleteTrait;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\ControllerTrait;
use CG\Stock\Adjustment\Service as AdjustmentService;
use CG\Stock\Location\Service;
use CG\Stock\Service as StockService;
use CG\Validation\PaginationInterface;
use Nocarrier\Hal;
use Slim\Slim;
use Zend\Di\Di;

class Location implements PaginationInterface
{
    use ControllerTrait;
    use GetTrait;
    use DeleteTrait;

    /** @var StockService */
    protected $stockService;

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this
            ->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function put($id, Hal $hal)
    {
        $adjustmentHeader = $this->slim->request->headers(AdjustmentService::ADJUSTMENT_HEADER, null);
        $adjustmentIds = ($adjustmentHeader != null ? explode(',', $adjustmentHeader) : []);
        try {
            $stockLocationHal = $this->getService()->saveHal($hal, ['id' => $id], $adjustmentIds);
        } catch (PermissionException $e) {
            throw new HttpNotFound('Entity Not Found', $e->getCode(), $e);
        }
        return $stockLocationHal;
    }
}
