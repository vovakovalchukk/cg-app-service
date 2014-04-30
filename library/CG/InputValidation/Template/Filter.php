<?php
namespace CG\InputValidation\Template;

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
        return array(
            'limit' => array(
                'name'       => 'limit',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(
                        ValidatorChain::Class,
                        [
                            'validators' => [
                                $this->getDi()->newInstance(Between::class, array('options' => array('min' => 1)))
                                    ->setMessages(array('notBetween' => 'limit should be at least %min%')),
                                $this->getDi()->newInstance(Identical::Class, ['token' => 'all'])
                                    ->setMessages([Identical::NOT_SAME => 'limit does not equal "%token%"'])
                            ]
                        ]
                    )
                )
            ),
            'page' => array(
                'name'       => 'page',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(Between::class, array('options' => array('min' => 1)))
                        ->setMessages(array('notBetween' => 'page should be at least %min%'))
                )
            ),
            'id' => array(
                'name'       => 'id',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, array('name' => 'id'))
                ),
            ),
            'organisationUnitId' => array(
                'name'       => 'organisationUnitId',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(ArrayOfIntegersValidator::class, array("name" => "organisationUnitId"))
                )
            ),
            'type' => array(
                'name'       => 'type',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, array('name' => 'type'))
                )
            )
        );
    }
}