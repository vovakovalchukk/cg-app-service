<?php
namespace CG\InputValidation\App\Service;

use CG\Validation\RulesInterface;
use Zend\Di\Di;

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
            'type' => array(
                'name'       => 'type',
                'required'   => true,
                'validators' => array(
                )
            ),
            'endpoint' => array(
                'name'       => 'endpoint',
                'required'   => true,
                'validators' => array(
                )
            ),
            'id' => array(
                'name'       => 'id',
                'required'   => false,
                'validators' => array(
                )
            )
        );
    }
}