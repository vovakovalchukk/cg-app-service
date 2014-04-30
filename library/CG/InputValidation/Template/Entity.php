<?php
namespace CG\InputValidation\Template;

use CG\Validation\RulesInterface;
use CG\Validation\Rules\DecimalValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use Zend\Di\Di;
use Zend\Validator\GreaterThan;
use Zend\Validator\StringLength;

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
                'name' => 'id',
                'required' => false,
                'validators' => array(
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                )
            ),
            'type' => array(
                'name' => 'type',
                'required' => true,
                'validators' => array(
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                )
            ),
            'organisationUnitId' => array(
                'name' => 'organisationUnitId',
                'required' => true,
                'validators' => array(
                    $this->getDi()->newInstance(IntegerValidator::class, ['name' => 'organisationUnitId']),
                    $this->getDi()->newInstance(GreaterThan::class, ['options' => ['min' => 1, 'inclusive' => true]])
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                )
            ),
            'minWidth' => array(
                'name' => 'minWidth',
                'required' => true,
                'validators' => array(
                    $this->getDi()->newInstance(DecimalValidator::class, ['name' => 'minWidth'])
                )
            ),
            'minHeight' => array(
                'name' => 'minHeight',
                'required' => true,
                'validators' => array(
                    $this->getDi()->newInstance(DecimalValidator::class, ['name' => 'minHeight'])
                )
            ),
            'elements'  => array(
                'name' => 'elements',
                'required' => true,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, ['name' => 'elements'])
                )
            )
        );
    }
}