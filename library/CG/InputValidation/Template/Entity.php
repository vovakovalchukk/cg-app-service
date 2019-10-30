<?php
namespace CG\InputValidation\Template;

use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
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
                'validators' => []
            ],
            'type' => [
                'name' => 'type',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'typeId' => [
                'name' => 'typeId',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'name' => [
                'name' => 'name',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])]
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
            'favourite' => [
                'name' => 'favourite',
                'required' => false,
                'validators' => [new BooleanValidator(['name' => 'favourite'])]
            ],
            'paperPage' => [
                'name' => 'paperPage',
                'required' => true,
                'validators' => []
            ],
            'printPage' => [
                'name' => 'printPage',
                'required' => true,
                'validators' => []
            ],
            'multiPerPage' => [
                'name' => 'multiPerPage',
                'required' => true,
                'validators' => []
            ],
            'elements'  => [
                'name' => 'elements',
                'required' => true,
                'validators' => [
                    new IsArrayValidator(["name" => "elements"])
                ]
            ],
            'editable' => [
                'name' => 'editable',
                'required' => false,
                'validators' => []
            ],
            'mongoId' => [
                'name' => 'mongoId',
                'required' => false,
                'validators' => []
            ]
        ];
    }
}