<?php
namespace CG\InputValidation\Ekm\Registration;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use CG\Validation\ValidatorChain;
use Zend\Di\Di;
use Zend\Validator\Between;
use Zend\Validator\Identical;
use CG\Validation\Rules\PaginationTrait;

class Filter implements RulesInterface
{
    use PaginationTrait;

    public function getRules()
    {
        return [
            'limit' => $this->getLimitValidation(),
            'page' => $this->getPageValidation(),
            'id' => [
                'name' => 'id',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), "id")
                ]
            ],
            'ekmUsername' => [
                'name'       => 'ekmUsername',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'ekmUsername'])
                ]
            ],
            'token' => [
                'name'       => 'token',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'token'])
                ]
            ],
            'organisationUnitId' => [
                'name'       => 'organisationUnitId',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
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