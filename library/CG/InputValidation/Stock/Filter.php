<?php
namespace CG\InputValidation\Stock;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use CG\Validation\ValidatorChain;
use Zend\Validator\Between;
use Zend\Validator\Identical;
use Zend\Di\Di;

class Filter implements RulesInterface
{
    protected $di;

    public function __construct(Di $di)
    {
        $this->setDi($di);
    }

    public function getRules()
    {
        return [
            'limit' => array(
                'name'       => 'limit',
                'required'   => false,
                'validators' => [
                    $this->getDi()->newInstance(
                        ValidatorChain::Class,
                        [
                            'validators' => [
                                $this->getDi()->newInstance(Between::class, ['options' => ['min' => 1]])
                                    ->setMessages(['notBetween' => 'limit should be at least %min%']),
                                $this->getDi()->newInstance(Identical::Class, ['token' => 'all'])
                                    ->setMessages([Identical::NOT_SAME => 'limit does not equal "%token%"'])
                            ]
                        ]
                    )
                ]
            ),
            'page' => [
                'name'       => 'page',
                'required'   => false,
                'validators' => [
                    $this->getDi()->newInstance(Between::class, ['options' => ['min' => 1]])
                        ->setMessages(['notBetween' => 'page should be at least %min%'])
                ]
            ],
            'id' => [
                'name'       => 'id',
                'required'   => false,
                'validators' => [
                    $this->getDi()->newInstance(ArrayOfIntegersValidator::class, array("name" => "id"))
                ]
            ],
            'organisationUnitId' => [
                'name'       => 'organisationUnitId',
                'required'   => false,
                'validators' => [
                    $this->getDi()->newInstance(ArrayOfIntegersValidator::class, array("name" => "organisationUnitId"))
                ]
            ],
            'sku' => [
                'name' => 'sku',
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(IsArrayValidator::class)
                ]
            ],
            'locationId' => [
                'name'       => 'locationId',
                'required'   => false,
                'validators' => [
                    $this->getDi()->newInstance(ArrayOfIntegersValidator::class, array("name" => "locationId"))
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