<?php
namespace CG\InputValidation\Template;

use CG\Validation\RulesInterface;
use CG\Validation\Rules\DecimalValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use Zend\Validator\GreaterThan;
use Zend\Validator\StringLength;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return array(
            'id' => array(
                'name' => 'id',
                'required' => false,
                'validators' => []
            ),
            'type' => array(
                'name' => 'type',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])]
            ),
            'typeId' => array(
                'name' => 'typeId',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])]
            ),
            'name' => array(
                'name' => 'name',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])]
            ),
            'organisationUnitId' => array(
                'name' => 'organisationUnitId',
                'required' => true,
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ),
            'paperPage' => array(
                'name' => 'paperPage',
                'required' => true,
                'validators' => []
            ),
            'elements'  => array(
                'name' => 'elements',
                'required' => true,
                'validators' => [
                    new IsArrayValidator(["name" => "elements"])
                ]
            ),
            'editable' => array(
                'name' => 'editable',
                'required' => false,
                'validators' => []
            ),
            'mongoId' => [
                'name' => 'mongoId',
                'required' => false,
                'validators' => []
            ]
        );
    }
}