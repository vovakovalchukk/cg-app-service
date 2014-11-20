<?php
namespace CG\InputValidation\Settings\Invoice;

use CG\Validation\RulesInterface;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\PaginationTrait;

class Filter implements RulesInterface
{
    use PaginationTrait;

    public function getRules()
    {
        $rules = $this->getPaginationValidation();
        $rules['id'] = [
            'name'       => 'id',
            'required'   => false,
            'validators' => [
                new IsArrayValidator(["name" => "id"])
            ]
        ];
        return $rules;
    }
}