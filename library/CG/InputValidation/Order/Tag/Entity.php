<?php
namespace CG\InputValidation\Order\Tag;

use CG\Validation\RulesInterface;
use Zend\Di\Di;
use CG\Validation\Rules\IntegerValidator;
use Zend\Validator\StringLength;
use Zend\Validator\GreaterThan;

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
                'required'   => true,
                'validators' => array(
                )
            ),
            'organisationUnitId' => array(
                'name'       => 'organisationUnitId',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(IntegerValidator::class, ['name' => 'organisationUnitId']),
                    $this->getDi()->newInstance(GreaterThan::class, ['options' => ['min' => 1, 'inclusive' => true]])
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                )
            ),
            'tag' => array(
                'name'       => 'tag',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                )
            ),
            'orderId' => array(
                'name'      => 'orderId',
                'required'  => true,
                'validators' => array(
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                )
            )
        );
    }
}