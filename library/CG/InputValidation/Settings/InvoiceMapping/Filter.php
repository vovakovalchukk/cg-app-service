<?php
namespace CG\InputValidation\Settings\InvoiceMapping;

use CG\Validation\Rules\IntegerValidator;
use Zend\Validator\GreaterThan;
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
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'id']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'id must be at least %min%'])
                ]
            ],
            'rootOuId' => [
                'name' => 'rootOuId',
                'required' => true,
                'validators' => [
                    new IntegerValidator(['name' => 'rootOuId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'rootOuId must be at least %min%'])
                ]
            ],
            'accountId' => [
                'name' => 'accountId',
                'required' => true,
                'validators' => [
                    new IntegerValidator(['name' => 'accountId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'accountId must be at least %min%'])
                ]
            ],
        ];
    }
}
