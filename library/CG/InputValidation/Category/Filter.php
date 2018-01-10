<?php
namespace CG\InputValidation\Category;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\IntegerValidator;
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
                'id' => [
                    'name'       => 'id',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'id')
                    ]
                ],
                'parentId' => [
                    'name'       => 'parentId',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'parentId')
                    ]
                ],
                'externalId' => [
                    'name'       => 'externalId',
                    'required'   => false,
                    'validators' => [
                        new IsArrayValidator(['name' => 'externalId'])
                    ]
                ],
                'channel' => [
                    'name'       => 'channel',
                    'required'   => false,
                    'validators' => [
                        new IsArrayValidator(['name' => 'channel'])
                    ]
                ],
                'listable' => [
                    'name'       => 'listable',
                    'required'   => false,
                    'validators' => [new BooleanValidator(['name' => 'listable'])]
                ],
                'marketplace' => [
                    'name'       => 'marketplace',
                    'required'   => false,
                    'validators' => [
                        new IsArrayValidator(['name' => 'marketplace'])
                    ]
                ],
                'enabled' => [
                    'name'       => 'enabled',
                    'required'   => false,
                    'validators' => [new BooleanValidator(['name' => 'enabled'])]
                ],
                'version' => [
                    'name'       => 'version',
                    'required'   => false,
                    'validators' => [
                        new ArrayOfIntegersValidator(new IntegerValidator(), 'version')
                    ]
                ],
            ]
        );
    }
}
