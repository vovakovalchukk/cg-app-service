<?php
namespace CG\InputValidation\RestExample;

use Zend\Di\Di;

use CG\Validation\ValidatorChain;

use Zend\Validator\Between;
use Zend\Validator\GreaterThan;
use Zend\Validator\LessThan;

class Filter
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
            'status' => array(
                'name'       => 'status',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(Between::class,['options' => ['min' => 1,'max' => 600]])
                         ->setMessages(['notBetween' => 'status should be between %min% & %max%'])
                )
            ),
            'chainTest1' => array(
                'name'       => 'chainTest1',
                'required'   => false,
                'validators' => array(
                    $this->getDi()
                         ->newInstance(ValidatorChain::class, ['validators' => [
                             $this->getDi()->newInstance(GreaterThan::class,['options' => ['min' => 20]]),
                             $this->getDi()->newInstance(LessThan::class,['options' => ['max' => 10]])]])
                         ->setMessages(['validationChainFail' => 'chainTest1 must be less than 10 or more than 20'])
                )
            ),
            'chainTest2' => array(
                'name'       => 'chainTest2',
                'required'   => false,
                'validators' => array(
                    $this->getDi()
                         ->newInstance(ValidatorChain::class, ['validators' => [
                             $this->getDi()->newInstance(GreaterThan::class,['options' => ['min' => 20]]),
                             $this->getDi()->newInstance(LessThan::class,['options' => ['max' => 10]])]])
                         ->setOr(false)
                )
            )
        );
    }
}