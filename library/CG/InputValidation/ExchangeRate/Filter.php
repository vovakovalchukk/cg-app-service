<?php
namespace CG\InputValidation\ExchangeRate;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\RulesInterface;
use CG\Validation\Rules\PaginationTrait;
use Zend\Validator\Date;

class Filter implements RulesInterface
{
    use PaginationTrait;

    public function getRules()
    {
        return array_merge(
            $this->getPaginationValidation(),
            [
                'id' => [
                    'name'       => 'id',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'id')
                    ]
                ],
                'date' => [
                    'name'       => 'date',
                    'required'   => false,
                    'validators' => [new Date(['format' => 'Y-m-d H:i:s'])]
                ]
            ]
        );
    }
}