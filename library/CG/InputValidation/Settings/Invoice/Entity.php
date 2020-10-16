<?php
namespace CG\InputValidation\Settings\Invoice;

use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\EmailValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\Date;
use Zend\Validator\GreaterThan;
use Zend\Validator\StringLength;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return [
            'id' => [
                'name' => 'id',
                'required' => false,
                'validators' => [
                    new IntegerValidator(['name' => 'id']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'id must be at least %min%'])
                ]
            ],
            'default' => [
                'name' => 'default',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'autoEmail' => [
                'name'       => 'autoEmail',
                'required'   => false,
                'validators' => [new Date(['format' => 'Y-m-d H:i:s'])]
            ],
            'autoEmailAllowed' => [
                'name' => 'autoEmailAllowed',
                'required' => false,
                'allow_empty' => true,
                'validators' => [new BooleanValidator(['name' => 'autoEmailAllowed'])],
            ],
            'emailSendAs' => [
                'name' => 'emailSendAs',
                'required' => false,
                'validators' => [new EmailValidator(['name' => 'emailSendAs'])]
            ],
            'emailVerified' => [
                'name' => 'emailVerified',
                'required' => false,
                'allow_empty' => true,
                'validators' => [new BooleanValidator(['name' => 'emailVerified'])],
            ],
            'emailVerificationStatus' => [
                'name' => 'emailVerificationStatus',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])],
            ],
            'emailBcc' => [
                'name' => 'emailBcc',
                'required' => false,
                'validators' => [new EmailValidator(['name' => 'emailBcc'])],
            ],
            'emailTemplate' => [
                'name' => 'emailTemplate',
                'required' => false,
                'allow_empty' => true,
                'validators' => [new EmailValidator(['name' => 'emailTemplate'])],
            ],
            'copyRequired' => [
                'name' => 'copyRequired',
                'required' => false,
                'allow_empty' => true,
                'validators' => [new BooleanValidator(['name' => 'copyRequired'])],
            ],
            'productImages' => [
                'name'       => 'productImages',
                'required'   => false,
                'allow_empty' => true,
                'validators' => [new BooleanValidator(['name' => 'productImages'])]
            ],
            'itemBarcodes' => [
                'name'       => 'itemBarcodes',
                'required'   => false,
                'allow_empty' => true,
                'validators' => [new BooleanValidator(['name' => 'itemBarcodes'])]
            ],
            'itemVariationAttributes' => [
                'name'       => 'itemVariationAttributes',
                'required'   => false,
                'allow_empty' => true,
                'validators' => [new BooleanValidator(['name' => 'itemVariationAttributes'])]
            ],
            'tradingCompanies' => [
                'name' => 'tradingCompanies',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "tradingCompanies"])
                ]
            ],
            'itemSku' => [
                'name' => 'itemSku',
                'required' => false,
                'allow_empty' => true,
                'validators' => [new BooleanValidator(['name' => 'itemSku'])]
            ],
            'useVerifiedEmailAddressForAmazonInvoices' => [
                'name' => 'useVerifiedEmailAddressForAmazonInvoices',
                'required' => false,
                'allow_empty' => true,
                'validators' => [new BooleanValidator(['name' => 'useVerifiedEmailAddressForAmazonInvoices'])]
            ],
            'sendToFba' => [
                'name'       => 'sendToFba',
                'required'   => false,
                'validators' => [new Date(['format' => 'Y-m-d H:i:s'])]
            ],
            'productLinks' => [
                'name' => 'productLinks',
                'required' => false,
                'validators' => [new BooleanValidator(['name' => 'productLinks'])]
            ],
            'mongoId' => [
                'name' => 'mongoId',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'additionalShippingLabels' => [
                'name'       => 'additionalShippingLabels',
                'required'   => false,
                'allow_empty' => true,
                'validators' => [new BooleanValidator(['name' => 'additionalShippingLabels'])]
            ],
        ];
    }
}
