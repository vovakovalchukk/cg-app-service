<?php
namespace CG\InputValidation\Order\Batch;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\Between;
use Zend\Validator\Identical;
use CG\Validation\Rules\BooleanValidator;
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
            'organisationUnitId' => array(
                'name'       => 'organisationUnitId',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                ]
            ),
            'active' => array(
                'name'       => 'active',
                'required'   => false,
                'allow_empty' => true,
                'validators' => [
                    new BooleanValidator(["name" => "active"])
                ]
            )
        );
    }
}