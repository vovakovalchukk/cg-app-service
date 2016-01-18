<?php
namespace CG\InputValidation\Stock\Log;

use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\Rules\IntegerValidator;
use Zend\Validator\Date as DateValidator;
use Zend\Validator\InArray;
use Zend\Validator\StringLength;

class Filter implements RulesInterface
{
    use PaginationTrait;

    public function getRules()
    {
        return array_merge(
            $this->getPaginationValidation(),
            [
                'organisationUnitId' => [
                    'name'       => 'organisationUnitId',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                    ]
                ],
                'sku' => [
                    'name' => 'sku',
                    'required' => false,
                    'validators' => [
                        new IsArrayValidator(["name" => "sku"])
                    ]
                ],
                'itemStatus' => [
                    'name'       => 'itemStatus',
                    'required'   => false,
                    'validators' => [
                        new IsArrayValidator(["name" => "itemStatus"])
                    ]
                ],
                'dateTimeFrom' => [
                    'name' => 'dateTimeFrom',
                    'required' => false,
                    'validators' => [
                        new DateValidator(['format' => StdlibDateTime::FORMAT])
                    ]
                ],
                'dateTimeTo' => [
                    'name' => 'dateTimeTo',
                    'required' => false,
                    'validators' => [
                        new DateValidator(['format' => StdlibDateTime::FORMAT])
                    ]
                ],
                'type' => [
                    'name'       => 'type',
                    'required'   => false,
                    'validators' => [
                        new IsArrayValidator(["name" => "type"])
                    ]
                ],
                'searchTerm' => [
                    'name' => 'searchTerm',
                    'required' => false,
                    'validators' => [new StringLength(['min' => 1])]
                ],
                'sortBy' => [
                    'name' => 'sortBy',
                    'required' => false,
                    'validators' => [new StringLength(['min' => 1])]
                ],
                'sortDirection' => [
                    'name' => 'sortDirection',
                    'required' => false,
                    'validators' => [
                        (new InArray())->setHaystack(['ASC','DESC'])
                    ]
                ],
            ]
        );
    }
}