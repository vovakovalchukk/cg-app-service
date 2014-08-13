<?php
namespace CG\InputValidation\Product;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;
use Zend\Validator\StringLength;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\BooleanValidator;
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
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                ]
            ],
            'name' => [
                'name'       => 'name',
                'required'   => true,
                'validators' => [
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                ]
            ],
            'parentProductId' => [
                'name'       => 'parentProductId',
                'required'   => true,
                'validators' => [
                    $this->getDi()->newInstance(IntegerValidator::class, ['name' => 'parentProductId']),
                    $this->getDi()->newInstance(GreaterThan::class, ['options' => ['min' => 0, 'inclusive' => true]])
                        ->setMessages(['notGreaterThanInclusive' => 'parentProductId must be at least %min%'])
                ]
            ],
            'attributeNames'  => [
                'name' => 'attributeNames',
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(IsArrayValidator::class, ['name' => 'attributeNames'])
                ]
            ],
            'attributeValues'  => [
                'name' => 'attributeValues',
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(IsArrayValidator::class, ['name' => 'attributeValues'])
                ]
            ],
            'deleted' => array(
                'name'       => 'deleted',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(BooleanValidator::class, ['options' => ['name' => 'deleted']])
                )
            ),
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