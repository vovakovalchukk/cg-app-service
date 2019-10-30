<?php
namespace CG\InputValidation\Template;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\RulesInterface;

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
            'organisationUnitId' => [
                'name'       => 'organisationUnitId',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                ]
            ],
            'type' => [
                'name'       => 'type',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(["name" => "type"])
                ]
            ],
            'favourite' => [
                'name' => 'favourite',
                'required' => false,
                'validators' => [new BooleanValidator(['name' => 'favourite'])]
            ],
        ];
    }
}