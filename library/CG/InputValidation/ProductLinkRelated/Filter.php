<?php
namespace CG\InputValidation\ProductLinkRelated;

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
                'ouIdProductSku' => [
                    'name' => 'ouIdProductSku',
                    'required' => false,
                    'validators' => [new IsArrayValidator(['name' => 'ouIdProductSku'])]
                ],
            ]
        );
    }
}