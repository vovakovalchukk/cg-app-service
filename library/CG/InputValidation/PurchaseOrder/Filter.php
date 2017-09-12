<?php
namespace CG\InputValidation\PurchaseOrder;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\Rules\ValidatorTrait;
use CG\Validation\RulesInterface;
use Zend\Di\Di;
use Zend\Validator\Date;

class Filter implements RulesInterface
{
    use ValidatorTrait;
    use PaginationTrait;

    public function __construct(Di $di)
    {
        $this->setDi($di);
    }

    public function getRules()
    {
        $rules = [
            'id' => [
                'name'       => 'id',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'id')
                ]
            ],
            'organisationUnitId' => [
                'name'       => 'organisationUnitId',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                ]
            ],
            'userId' => [
                'name'       => 'userId',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'userId'])
                ]
            ],
            'status' => [
                'name'       => 'status',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'status'])
                ]
            ],
            'externalId' => [
                'name'       => 'externalId',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'externalId'])
                ]
            ],
            'createdFrom' => [
                'name' => 'createdFrom',
                'required' => false,
                'validators' => [
                    new Date(['format' => "Y-m-d H:i:s"])
                ]
            ],
            'createdTo' => [
                'name' => 'createdTo',
                'required' => false,
                'validators' => [
                    new Date(['format' => "Y-m-d H:i:s"])
                ]
            ],
        ];

        return array_merge($this->getPaginationValidation(), $rules);
    }
}
