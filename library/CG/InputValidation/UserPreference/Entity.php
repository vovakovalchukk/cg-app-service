<?php
namespace CG\InputValidation\UserPreference;

use CG\Validation\RulesInterface;
use Zend\Di\Di;
use CG\Validation\Rules\IsArrayValidator;

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
            'preference' => array(
                'name'       => 'preference',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, array('name' => 'preference'))
                )
            )
        );
    }
}