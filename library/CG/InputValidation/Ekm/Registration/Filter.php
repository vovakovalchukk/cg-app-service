<?php
namespace CG\InputValidation\Ekm\Registration;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\RulesInterface;
use CG\Validation\ValidatorChain;
use Zend\Di\Di;
use Zend\Validator\Between;
use Zend\Validator\Identical;

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
                    new ValidatorChain(
                        [
                            (new Between(['min' => 1]))
                                ->setMessages(['notBetween' => 'limit should be at least %min%']),
                            (new Identical('all'))
                                ->setMessages([Identical::NOT_SAME => 'limit does not equal "%token%"'])
                        ]
                    )
                ]
            ),
            'page' => [
                'name'       => 'page',
                'required'   => false,
                'validators' => [
                    (new Between(['min' => 1]))
                        ->setMessages(['notBetween' => 'page should be at least %min%'])
                ]
            ],
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
            'rootOrganisationUnitId' => [
                'name'       => 'rootOrganisationUnitId',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'rootOrganisationUnitId')
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