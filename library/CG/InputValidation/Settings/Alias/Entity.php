<?php
namespace CG\InputValidation\Settings\Alias;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\StringLength;
use Zend\Di\Di;

class Entity implements RulesInterface
{
    protected $di;

    public function __construct(Di $di)
    {
        $this->setDi($di);
    }

    public function getRules()
    {
        return [
            'id' => [
                'name'       => 'id',
                'required'   => false,
                'validators' => []
            ],
            'name' => [
                'name'       => 'name',
                'required'   => true,
                'validators' => [
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                ]
            ],
            'methodIds' => [
                'name' => 'methodIds',
                'required' => true,
                'validators' => [
                    $this->getDi()->newInstance(ArrayOfIntegersValidator::class, ["name" => "methodIds"])
                ]
            ]
        ];
    }

    public function setDi($di)
    {
        $this->di = $di;
        return $this;
    }

    public function getDi()
    {
        return $this->di;
    }
}