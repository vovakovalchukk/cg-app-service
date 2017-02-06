<?php
namespace CG\InputValidation\Order\Item;

use CG\Validation\RulesInterface;

class Images implements RulesInterface
{
    use ImageValidationTrait;

    public function getRules()
    {
        return [
            'imageIds' => $this->getImageValidationRules('imageIds'),
        ];
    }
}
