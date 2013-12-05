<?php
namespace CG\InputValidation\Order\Tracking;

use CG\Validation\RulesInterface;
use Zend\Di\Di;
use Zend\Validator\Date;
use CG\Validation\Rules\IntegerValidator;
use Zend\Validator\StringLength;
use Zend\Validator\GreaterThan;

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
            'id' => array(
                'name'       => 'id',
                'required'   => false,
                'validators' => array(
                )
            ),
            'orderId' => array(
                'name'       => 'orderId',
                'required'   => false,
                'validators' => array(
                )
            ),
            'userId' => array(
                'name'       => 'userId',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(IntegerValidator::class, ['name' => 'userId']),
                    $this->getDi()->newInstance(GreaterThan::class, ['options' => ['min' => 1, 'inclusive' => true]])
                        ->setMessages(['notGreaterThanInclusive' => 'userId must be at least %min%'])
                )
            ),
            'number' => array(
                'name'       => 'number',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                )
            ),
            'carrier' => array(
                'name'       => 'carrier',
                'required'   => true,
                'validators' => array(
                )
            ),
            'timestamp' => array(
                'name'       => 'timestamp',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(Date::class, array('options' => array('format' => "Y-m-d H:i:s")))
                )
            )
        );
    }
}