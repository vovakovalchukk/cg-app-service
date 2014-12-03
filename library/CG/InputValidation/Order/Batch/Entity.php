<?php
namespace CG\InputValidation\Order\Batch;

use CG\Validation\RulesInterface;
use CG\Validation\Rules\IntegerValidator;
use Zend\Validator\StringLength;
use Zend\Validator\GreaterThan;
use CG\Validation\Rules\BooleanValidator;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return array(
            'id' => array(
                'name'       => 'id',
                'required'   => true,
                'validators' => array(
                )
            ),
            'organisationUnitId' => array(
                'name'       => 'organisationUnitId',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ),
            'name' => array(
                'name'       => 'name',
                'required'   => true,
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                    ->setMessages(['notGreaterThanInclusive' => 'name must be at least %min%'])]
            ),
            'active' => array(
                'name'       => 'active',
                'required'   => false,
                'validators' => [new BooleanValidator(['name' => 'active'])]
            )
        );
    }
}