<?php
namespace CG\InputValidation\Shipping\Method;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\RulesInterface;
use Zend\Di\Di;
use Zend\Validator\Between;
use Zend\Validator\Identical;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\ValidatorChain;

class Filter implements RulesInterface
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
        return [
            'limit' => [
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
            ],
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
                    $this->getDi()->newInstance(IsArrayValidator::class, ['name' => 'id'])
                ],
            ],
            'channel' => [
                'name'       => 'channel',
                'required'   => false,
                'validators' => [
                    $this->getDi()->newInstance(IsArrayValidator::class, ['name' => 'channel'])
                ]
            ],
            'method' => [
                'name'       => 'method',
                'required'   => false,
                'validators' => [
                    $this->getDi()->newInstance(IsArrayValidator::class, ['name' => 'method'])
                ]
            ],
            'organisationUnitId' => [
                'name'       => 'organisationUnitId',
                'required'   => false,
                'validators' => [
                    $this->getDi()->newInstance(ArrayOfIntegersValidator::class, ["name" => "organisationUnitId"])
                ]
            ]
        ];
    }
}