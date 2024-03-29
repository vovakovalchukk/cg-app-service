<?php
namespace CG\InputValidation\OrderCounts;

use CG\Locale\CurrencyCode;
use CG\Locale\CountryCode;
use CG\Validation\RulesInterface;
use CG\Validation\Rules\DecimalValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use Zend\Validator\Date;
use Zend\Validator\GreaterThan;
use Zend\Validator\InArray;
use Zend\Validator\StringLength;
use Zend\Validator\Callback;

use CG\Order\Shared\OrderCounts\Repository as OrderCountsRepository;

class Entity implements RulesInterface
{
    protected $orderCountsRepository;

    public function __construct(OrderCountsRepository $orderCountsRepository)
    {
        $this->setOrderCountsRepository($orderCountsRepository);
    }

    public function getRules()
    {
        return array(
            'batches' => array(
                'name'       => 'batches',
                'required'   => false,
                'validators' => []
            ),
            'status' => array(
                'name'       => 'status',
                'required'   => false,
                'validators' => []
            ),
            'allOrders' => array(
                'name'       => 'allOrders',
                'required'   => false,
                'validators' => []
            ),
            'awaitingPayment' => array(
                'name'       => 'awaitingPayment',
                'required'   => false,
                'validators' => []
            ),
            'newOrders' => array(
                'name'       => 'newOrders',
                'required'   => false,
                'validators' => []
            ),
            'processing' => array(
                'name'       => 'processing',
                'required'   => false,
                'validators' => []
            ),
            'dispatched' => array(
                'name'       => 'dispatched',
                'required'   => false,
                'validators' => []
            ),
            'cancelledAndRefunded' => array(
                'name'       => 'cancelledAndRefunded',
                'required'   => false,
                'validators' => []
            )
        );         
    }

    protected function setOrderCountsRepository($orderCountsRepository)
    {
        $this->orderCountsRepository = $orderCountsRepository;
        return $this;
    }

    protected function getOrderCountsRepository()
    {
        return $this->orderCountsRepository;
    }
}