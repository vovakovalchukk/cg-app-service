<?php
namespace CG\InputValidation\Order\Item;

use CG\Validation\Rules\IsArrayValidator;

trait ImageValidationTrait
{
    protected function getImageValidationRules($name = 'imageIds')
    {
        return [
            'name' => $name,
            'required' => false,
            'validators' => [new IsArrayValidator(['name' => $name])]
        ];
    }
}
