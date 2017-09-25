<?php
namespace CG\InputValidation\Ekm\Registration;

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
            'ekmUsername' => [
                'name' => 'ekmUsername',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'json' => [
                'name' => 'json',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'referrer' => [
                'name' => 'referrer',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'application' => [
                'name' => 'application',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'token' => [
                'name' => 'token',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'organisationUnitId' => [
                'name'       => 'organisationUnitId',
                'required'   => false,
                'validators' => [new IntegerValidator(['name' => 'organisationUnitId'])]
            ],
            'createdDate' => [
                'name' => 'createdDate',
                'required' => false,
                'validators' => [new Date(['format' => 'Y-m-d H:i:s'])]
            ],
            'completedDate' => [
                'name' => 'completedDate',
                'required' => false,
                'validators' => [new Date(['format' => 'Y-m-d H:i:s'])]
            ]
        ];
    }
}