<?php
namespace CG\InputValidation\Order\Order;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\RulesInterface;
use Zend\Di\Di;
use Zend\Validator\Between;
use Zend\Validator\Date;
use Zend\Validator\InArray;
use Zend\Validator\Identical;
use CG\Validation\Rules\BooleanValidator;
use CG\Constant\CountryCode;
use CG\Constant\CurrencyCode;
use CG\Validation\Rules\InArrayValidator;
use CG\Validation\Rules\IsArrayValidator;
use Zend\Validator\StringLength;
use CG\Validation\ValidatorChain;
use CG\Validation\Rules\DecimalValidator;

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
            'orderFilter' => array(
                'name'       => 'orderFilter',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                )
            ),

        );
    }
}