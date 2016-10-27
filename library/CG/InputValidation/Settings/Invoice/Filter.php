<?php
namespace CG\InputValidation\Settings\Invoice;

use CG\Validation\Rules\BooleanValidator;
use CG\Validation\RulesInterface;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\PaginationTrait;

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
            'emailSendAs' => [
                'name' => 'emailSendAs',
                'required' => false,
                'validators' => []
            ],
            'emailVerified' => [
                'name' => 'emailVerified',
                'required' => false,
                'validators' => [
                    new BooleanValidator(['name' => 'emailVerified'])
                ]
            ],
            'pendingVerification' => [
                'name' => 'pendingVerification',
                'required' => false,
                'validators' => [
                    new BooleanValidator(['name' => 'pendingVerification'])
                ]
            ]
        ];
    }
}