<?php
namespace CG\InputValidation\PurchaseOrder;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use CG\Validation\Rules\ValidatorTrait;
use Zend\Di\Di;
use Zend\Validator\Date;
use Zend\Validator\GreaterThan;

class Entity implements RulesInterface
{
    use ValidatorTrait;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function getRules()
    {
        return [
            'id' => [
                'name'       => 'id',
                'required'   => false,
                'validators' => [
                    new IntegerValidator(['name' => 'id']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'id must be at least %min%'])
                ]
            ],
            'organisationUnitId' => [
                'name'       => 'organisationUnitId',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ],
            'userId' => [
                'name'       => 'userId',
                'required'   => true,
                'validators' => []
            ],
            'status' => [
                'name'       => 'status',
                'required'   => true,
                'validators' => []
            ],
            'externalId' => [
                'name'       => 'externalId',
                'required'   => true,
                'validators' => []
            ],
            'created' => [
                'name'       => 'created',
                'required'   => true,
                'validators' => [new Date(['format' => 'Y-m-d H:i:s'])]
            ],
        ];
    }
}
