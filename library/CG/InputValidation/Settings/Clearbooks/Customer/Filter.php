<?php
namespace CG\InputValidation\Settings\Clearbooks\Customer;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\RulesInterface;
use CG\Validation\ValidatorChain;
use Zend\Validator\Between;
use Zend\Validator\Identical;

class Filter implements RulesInterface
{
    use PaginationTrait;

    public function getRules()
    {
        return [
            'limit' => $this->getLimitValidation(),
            'page' => $this->getPageValidation(),
            'organisationUnitId' => array(
                'name'       => 'organisationUnitId',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                ]
            ),
        ];
    }
}