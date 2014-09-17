<?php
namespace CG\InputValidation\Product;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use CG\Validation\ValidatorChain;
use Zend\Validator\Between;
use Zend\Validator\Identical;
use Zend\Validator\StringLength;
use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\PaginationTrait;
use Zend\Di\Di;

class Filter implements RulesInterface
{
    use PaginationTrait;

    protected $di;

    public function __construct(Di $di)
    {
        $this->setDi($di);
    }

    public function getRules()
    {
        return array_merge(
            $this->getPaginationValidation(),
            [
                'organisationUnitId' => [
                    'name'       => 'organisationUnitId',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                    ]
                ],
                'searchTerm' => [
                    'name' => 'searchTerm',
                    'required' => false,
                    'validators' => [
                        new StringLength(['min' => 1])
                    ]
                ],
                'parentProductId' => [
                    'name'       => 'parentProductId',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'parentProductId')
                    ]
                ],
                'id' => [
                    'name'       => 'id',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'id')
                    ]
                ],
                'deleted' => [
                    'name'       => 'deleted',
                    'required'   => false,
                    'validators' => [
                        new BooleanValidator(['name' => 'deleted'])
                    ]
                ],
                'sku' => [
                    'name'       => 'sku',
                    'required'   => false,
                    'validators' => [
                        new IsArrayValidator(['name' => 'sku'])
                    ]
                ]
            ]
        );
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