<?php
namespace CG\InputValidation\Settings\Invoice;

use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\EmailValidator;
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
                'allow_empty' => true,
                'validators' => [
                    new BooleanValidator(['name' => 'emailVerified'])
                ]
            ],
            'emailBcc' => [
                'name' => 'emailBcc',
                'required' => false,
                'validators' => [
                    new EmailValidator(['name' => 'emailBcc'])
                ]
            ],
            'copyRequired' => [
                'name' => 'copyRequired',
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    new BooleanValidator(['name' => 'copyRequired'])
                ]
            ],
            'pendingVerification' => [
                'name' => 'pendingVerification',
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    new BooleanValidator(['name' => 'pendingVerification'])
                ]
            ],
            'verifiedEmail' => [
                'name' => 'verifiedEmail',
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    new BooleanValidator(['name' => 'verifiedEmail'])
                ]
            ]
        ];
    }
}