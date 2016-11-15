<?php
namespace CG\InputValidation\ExchangeRate;

use CG\Validation\RulesInterface;
use Zend\Validator\Date;

class Entity implements RulesInterface
{
    public function getRules()
    {
        return [
            'id' => [
                'name'       => 'id',
                'required'   => false,
                'validators' => []
            ],
            'date' => [
                'name'       => 'date',
                'required'   => false,
                'validators' => [new Date(['format' => 'Y-m-d H:i:s'])]
            ]
        ];
    }
}