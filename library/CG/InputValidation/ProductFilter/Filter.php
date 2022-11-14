<?php
namespace CG\InputValidation\ProductFilter;

use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;

class Filter implements RulesInterface
{
    use PaginationTrait;

    public function getRules(): array
    {
        return array_merge(
            $this->getPaginationValidation(),
            [
                'id' => [
                    'name' => 'id',
                    'required' => false,
                    'validators' => [
                        new IntegerValidator(['name' => 'id']),
                        new GreaterThan(['min' => 1, 'inclusive' => true]),
                    ],
                ],
                'organisationUnitId' => [
                    'name' => 'organisationUnitId',
                    'required' => false,
                    'validators' => [
                        new IntegerValidator(['name' => 'organisationUnitId']),
                        new GreaterThan(['min' => 1, 'inclusive' => true]),
                    ],
                ],
                'userId' => [
                    'name' => 'userId',
                    'required' => false,
                    'validators' => [
                        new IntegerValidator(['name' => 'userId']),
                        new GreaterThan(['min' => 1, 'inclusive' => true]),
                    ],
                ],
                'defaultFilter' => [
                    'name'       => 'defaultFilter',
                    'required'   => false,
                    'validators' => [new BooleanValidator(['name' => 'defaultFilter'])],
                ],
            ]
        );
    }
}