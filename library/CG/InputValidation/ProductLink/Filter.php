<?php
namespace CG\InputValidation\ProductLink;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\RulesInterface;

class Filter implements RulesInterface
{
    use PaginationTrait;

    public function getRules()
    {
        return array_merge(
            $this->getPaginationValidation(),
            [
                'organisationUnitId' => [
                    'name' => 'organisationUnitId',
                    'required' => false,
                    'validators' => [new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')]
                ],
                'productSku' => [
                    'name' => 'productSku',
                    'required' => false,
                    'validators' => [new IsArrayValidator(['name' => 'productSku'])]
                ],
                'stockSku' => [
                    'name' => 'stockSku',
                    'required' => false,
                    'validators' => [new IsArrayValidator(['name' => 'stockSku'])]
                ],
            ]
        );
    }
}