<?php
namespace CG\InputValidation\Settings\Alias;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use Zend\Validator\GreaterThan;
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
            'organisationUnitId' => [
                'name'       => 'organisationUnitId',
                'required'   => true,
                'validators' => [
                    $this->getDi()->newInstance(IntegerValidator::class, ['name' => 'organisationUnitId']),
                    $this->getDi()->newInstance(GreaterThan::class, ['options' => ['min' => 1, 'inclusive' => true]])
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ],
            'methodIds' => [
                'name' => 'methodIds',
                'required' => false,
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