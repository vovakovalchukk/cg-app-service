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
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'id']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'id must be at least %min%'])
                ]
            ],
            'organisationUnitId' => [
                'name' => 'organisationUnitId',
                'required' => true,
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ],
            'invoiceId' => [
                'name' => 'invoiceId',
                'required' => true,
                'validators' => [
                    new IntegerValidator(['name' => 'invoiceId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'invoiceId must be at least %min%'])
                ]
            ],
            'sendViaEmail' => [
                'name' => 'sendViaEmail',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'sendToFba' => [
                'name' => 'sendToFba',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
        ];
    }
}
