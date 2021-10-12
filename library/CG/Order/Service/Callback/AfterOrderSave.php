<?php
namespace CG\Order\Service\Callback;

use CG\Amazon\Gearman\Generator\UploadInvoiceForOrder;
use CG\Order\Callback\Service\AfterOrderSaveInterface;
use CG\Order\Client\Gearman\Generator\AutoEmailInvoice as AutoEmailInvoiceGenerator;
use CG\Order\Client\Gearman\Generator\CalculateOrderWeight as CalculateOrderWeightGenerator;
use CG\Order\Client\Gearman\Generator\LinkMatchingOrders as LinkMatchingOrdersGenerator;
use CG\Order\Client\Gearman\Generator\SaveOrderShippingMethod as SaveOrderShippingMethodGenerator;
use CG\Order\Client\Gearman\Generator\UpdateCustomerOrderCount as UpdateCustomerOrderCountGenerator;
use CG\Order\Client\Gearman\Generator\UpdateExchangeRate;
use CG\Order\Client\Gearman\Generator\UpdateOrderCount as UpdateOrderCountGenerator;
use CG\Order\Shared\Entity as Order;

class AfterOrderSave implements AfterOrderSaveInterface
{
    /** @var UploadInvoiceForOrder */
    protected $uploadInvoiceForOrder;
    /** @var AutoEmailInvoiceGenerator */
    protected $autoEmailInvoiceGenerator;
    /** @var CalculateOrderWeightGenerator */
    protected $calculateOrderWeightGenerator;
    /** @var SaveOrderShippingMethodGenerator */
    protected $saveOrderShippingMethodGenerator;
    /** @var UpdateOrderCountGenerator */
    protected $updateOrderCountGenerator;
    /** @var LinkMatchingOrdersGenerator */
    protected $linkMatchingOrdersGenerator;
    /** @var UpdateExchangeRate */
    protected $exchangeRateUpdater;
    /** @var UpdateCustomerOrderCountGenerator */
    protected $updateCustomerOrderCountGenerator;

    public function __construct(
        UploadInvoiceForOrder $uploadInvoiceForOrder,
        AutoEmailInvoiceGenerator $autoEmailInvoiceGenerator,
        CalculateOrderWeightGenerator $calculateOrderWeightGenerator,
        SaveOrderShippingMethodGenerator $saveOrderShippingMethodGenerator,
        UpdateOrderCountGenerator $updateOrderCountGenerator,
        LinkMatchingOrdersGenerator $linkMatchingOrdersGenerator,
        UpdateExchangeRate $exchangeRateUpdater,
        UpdateCustomerOrderCountGenerator $updateCustomerOrderCountGenerator
    ) {
        $this->uploadInvoiceForOrder = $uploadInvoiceForOrder;
        $this->autoEmailInvoiceGenerator = $autoEmailInvoiceGenerator;
        $this->calculateOrderWeightGenerator = $calculateOrderWeightGenerator;
        $this->saveOrderShippingMethodGenerator = $saveOrderShippingMethodGenerator;
        $this->updateOrderCountGenerator = $updateOrderCountGenerator;
        $this->linkMatchingOrdersGenerator = $linkMatchingOrdersGenerator;
        $this->exchangeRateUpdater = $exchangeRateUpdater;
        $this->updateCustomerOrderCountGenerator = $updateCustomerOrderCountGenerator;
    }

    public function triggerCallbacksForExistingOrder(Order $order, Order $existingOrder): void
    {
        $this->updateOrderCountGenerator->createJob($order, $existingOrder);
        $this->saveOrderShippingMethodGenerator->createJob($order, $existingOrder);
        $this->triggerCallbacksForOrder($order);
    }

    public function triggerCallbacksForNewOrder(Order $order): void
    {
        $this->calculateOrderWeightGenerator->generateJobForOrder($order);
        $this->updateOrderCountGenerator->createJob($order);
        $this->saveOrderShippingMethodGenerator->createJob($order);
        $this->updateCustomerOrderCountGenerator->generateJobForOrder($order);
        $this->triggerCallbacksForOrder($order);
    }

    protected function triggerCallbacksForOrder(Order $order): void
    {
        $this->autoEmailInvoiceGenerator->createJobForOrder($order);
        $this->uploadInvoiceForOrder->generateJobForOrder($order);
        $this->linkMatchingOrdersGenerator->generateForOrder($order);
        $this->exchangeRateUpdater->addJobForOrder($order);
    }
}
