<?php
namespace CG\InputValidation\Shipping\Method;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\RulesInterface;
use Zend\Di\Di;
use Zend\Validator\Between;
use Zend\Validator\Identical;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\ValidatorChain;
use CG\Validation\Rules\IntegerValidator;

class Filter implements RulesInterface
{
    use PaginationTrait;

    public function getRules()
    {
        return [
            'limit' => $this->getLimitValidation(),
            'page' => $this->getPageValidation(),
            'id' => [
                'name'       => 'id',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(["name" => "id"])
                ]
            ],
            'channel' => [
                'name'       => 'channel',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(["name" => "channel"])
                ]
            ],
            'method' => [
                'name'       => 'method',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(["name" => "method"])
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
}