<?php
namespace CG\InputValidation\Order\Archive;

use CG\Validation\RulesInterface;
use Zend\Di\Di;
use Zend\Validator\InArray;

class Entity implements RulesInterface
{
    protected $di;

    public function __construct(Di $di)
    {
        $this->setDi($di);
    }

    protected function getDi()
    {
        return $this->di;
    }

    protected function setDi(Di $di)
    {
        $this->di = $di;
    }

    public function getRules()
    {
        return array(
            'orderId' => array(
                'name'       => 'orderId',
                'required'   => false,
                'validators' => array(
                )
            ),
            'archived' => array(
                'name'       => 'archived',
                'required'   => false,
                'allowEmpty' => true,
                'continueIfEmpty' => true,
                'validators' => array(
                    $this->getDi()->newInstance(InArray::class)
                                  ->setHaystack(array(1, "true", 0, "false", "1", "0", true, false, ""))
                )
            )
        );
    }
}