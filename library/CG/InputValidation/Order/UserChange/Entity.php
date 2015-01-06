<?php
namespace CG\InputValidation\Order\UserChange;

use CG\Validation\RulesInterface;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\IntegerValidator;
use Zend\Validator\GreaterThan;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return array(
            'orderId' => array(
                'name'       => 'orderId',
                'required'   => false,
                'validators' => []
            ),
            'organisationUnitId' => array(
                'name'       => 'organisationUnitId',
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ),
            'changes' => array(
                'name'       => 'changes',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(["name" => "changes"])
                ]
            )
        );
    }
}