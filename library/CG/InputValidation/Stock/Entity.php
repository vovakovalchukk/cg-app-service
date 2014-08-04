<?php
namespace CG\InputValidation\Stock;

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
                'required'   => false,
                'validators' => []
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
            'sku' => [
                'name' => 'sku',
                'required' => true,
                'validators' => [
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
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