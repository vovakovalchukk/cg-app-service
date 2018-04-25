<?php
namespace CG\InputValidation\CategoryTemplate;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
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
                'id' => [
                    'name' => 'id',
                    'required' => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'id')
                    ]
                ],
                'organisationUnitId' => [
                    'name' => 'organisationUnitId',
                    'required' => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                    ]
                ],
                'categoryId' => [
                    'name' => 'categoryId',
                    'required' => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'categoryId')
                    ]
                ],
                'accountId' => [
                    'name' => 'accountId',
                    'required' => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'accountId')
                    ]
                ],
                'search' => [
                    'name' => 'search',
                    'required' => false,
                    'validators' => [new StringLength(['min' => 1])]
                ],
            ]
        );
    }
}