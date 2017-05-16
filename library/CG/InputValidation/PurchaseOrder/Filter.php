<?php
namespace CG\InputValidation\PurchaseOrder;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IsArrauValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\Rules\ValidatorTrait;
use CG\Validation\RulesInterface;
use Zend\Di\Di;

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
            'status' => [
                'name'       => 'status',
                'required'   => false,
                'validators' => []
            ],
            'externalId' => [
                'name'       => 'externalId',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'id')
                ]
            ]
        ];

        return array_merge($this->getPaginationValidation(), $rules);
    }
}
