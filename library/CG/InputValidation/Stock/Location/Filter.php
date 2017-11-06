<?php
namespace CG\InputValidation\Stock\Location;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\RulesInterface;
use Zend\Validator\StringLength;

class Filter implements RulesInterface
{
    use PaginationTrait;

    public function getRules()
    {
        return array_merge(
            $this->getPaginationValidation(),
            [
                'XDEBUG_SESSION_START' => [
                    'name' => 'XDEBUG_SESSION_START',
                    'required' => false,
                    'validators' => [new StringLength(1)],
                ],
                'stockId' => [
                    'name'       => 'stockId',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'stockId')
                    ]
                ],
                'locationId' => [
                    'name'       => 'locationId',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'locationId')
                    ]
                ],
                'ouIdSku' => [
                    'name'       => 'ouIdSku',
                    'required'   => false,
                    'validators' => [
                        new IsArrayValidator(['name' => 'ouIdSku'])
                    ]
                ],
            ]
        );
    }
}