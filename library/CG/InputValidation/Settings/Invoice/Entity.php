<?php
namespace CG\InputValidation\Settings\Invoice;

use CG\Validation\RulesInterface;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use Zend\Di\Di;
use Zend\Validator\GreaterThan;

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
                'name' => 'id',
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(IntegerValidator::class, ['name' => 'id']),
                    $this->getDi()->newInstance(GreaterThan::class, ['options' => ['min' => 1, 'inclusive' => true]])
                        ->setMessages(['notGreaterThanInclusive' => 'id must be at least %min%'])
                ]
            ],
            'default' => [
                'name' => 'default',
                'required' => true,
                'validators' => [
                    $this->getDi()->newInstance(IntegerValidator::class, ['name' => 'default']),
                    $this->getDi()->newInstance(GreaterThan::class, ['options' => ['min' => 1, 'inclusive' => true]])
                        ->setMessages(['notGreaterThanInclusive' => 'default must be at least %min%'])
                ]
            ],
            'tradingCompanies' => [
                'name' => 'tradingCompanies',
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(IsArrayValidator::class, ['name' => 'tradingCompanies'])
                ]
            ],
        ];
    }

    protected function getDi()
    {
        return $this->di;
    }

    protected function setDi(Di $di)
    {
        $this->di = $di;
    }
}