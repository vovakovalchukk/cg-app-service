<?php
namespace CG\InputValidation\Stock\Location;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;
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
                'required'   => true,
                'validators' => [
                    $this->getDi()->newInstance(IntegerValidator::class, ['name' => 'id']),
                    $this->getDi()->newInstance(GreaterThan::class, ['options' => ['min' => 1, 'inclusive' => true]])
                        ->setMessages(['notGreaterThanInclusive' => 'id must be at least %min%'])
                ]
            ],
            'stockId' => [
                'name'       => 'stockId',
                'required'   => true,
                'validators' => [
                    $this->getDi()->newInstance(IntegerValidator::class, ['name' => 'stockId']),
                    $this->getDi()->newInstance(GreaterThan::class, ['options' => ['min' => 1, 'inclusive' => true]])
                        ->setMessages(['notGreaterThanInclusive' => 'stockId must be at least %min%'])
                ]
            ],
            'onHand' => [
                'name'       => 'onHand',
                'required'   => true,
                'validators' => [
                    $this->getDi()->newInstance(IntegerValidator::class, ['name' => 'onHand']),
                    $this->getDi()->newInstance(GreaterThan::class, ['options' => ['min' => 0, 'inclusive' => true]])
                        ->setMessages(['notGreaterThanInclusive' => 'onHand must be at least %min%'])
                ]
            ],
            'allocated' => [
                'name'       => 'allocated',
                'required'   => true,
                'validators' => [
                    $this->getDi()->newInstance(IntegerValidator::class, ['name' => 'allocated']),
                    $this->getDi()->newInstance(GreaterThan::class, ['options' => ['min' => 0, 'inclusive' => true]])
                        ->setMessages(['notGreaterThanInclusive' => 'allocated must be at least %min%'])
                ]
            ],
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