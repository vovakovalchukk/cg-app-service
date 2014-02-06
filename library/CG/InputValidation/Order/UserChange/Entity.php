<?php
namespace CG\InputValidation\Order\UserChange;

use CG\Validation\RulesInterface;
use Zend\Di\Di;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\IntegerValidator;
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
            'orderId' => array(
                'name'       => 'orderId',
                'required'   => false,
                'validators' => array(
                )
            ),
            'organisationUnitId' => array(
                'name'       => 'organisationUnitId',
                'validators' => array(
                    $this->getDi()->newInstance(IntegerValidator::class, ['name' => 'organisationUnitId']),
                    $this->getDi()->newInstance(GreaterThan::class, ['options' => ['min' => 1, 'inclusive' => true]])
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                )
            ),
            'changes' => array(
                'name'       => 'changes',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, array('name' => 'changes'))
                )
            )
        );
    }
}