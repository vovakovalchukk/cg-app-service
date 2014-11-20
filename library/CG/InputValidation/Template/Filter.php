<?php
namespace CG\InputValidation\Template;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\Between;
use Zend\Validator\Identical;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\ValidatorChain;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\Rules\IntegerValidator;

class Filter implements RulesInterface
{
    use PaginationTrait;

    public function getRules()
    {
        return array(
            'limit' => $this->getLimitValidation(),
            'page' => $this->getPageValidation(),
            'id' => array(
                'name'       => 'id',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(["name" => "id"])
                ]
            ),
            'organisationUnitId' => array(
                'name'       => 'organisationUnitId',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                ]
            ),
            'type' => array(
                'name'       => 'type',
                'required'   => false,
                'validators' => [
                    new IsArrayValidator(["name" => "type"])
                ]
            )
        );
    }
}