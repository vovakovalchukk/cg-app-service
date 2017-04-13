<?php
namespace CG\InputValidation\Settings\InvoiceMapping;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;
use Zend\Validator\StringLength;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return [
            'id' => [
                'name'       => 'id',
                'required'   => false,
                'validators' => [
                    new IntegerValidator(['name' => 'id']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'id must be at least %min%'])
                ]
            ],
            'organisationUnitId' => [
                'name' => 'organisationUnitId',
                'required' => false,
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ],
            'site' => [
                'name' => 'site',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'invoiceId' => [
                'name' => 'invoiceId',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'accountId' => [
                'name' => 'accountId',
                'required' => false,
                'validators' => [
                    new IntegerValidator(['name' => 'accountId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'accountId must be at least %min%'])
                ]
            ],
            'sendViaEmail' => [
                'name' => 'sendViaEmail',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'sendToFba' => [
                'name' => 'sendToFba',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
        ];
    }
}
