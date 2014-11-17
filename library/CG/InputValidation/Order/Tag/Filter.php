<?php
namespace CG\InputValidation\Order\Tag;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use Zend\Di\Di;
use Zend\Validator\Between;
use Zend\Validator\Identical;
use Zend\Validator\StringLength;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\ValidatorChain;
use CG\Validation\Rules\PaginationTrait;

class Filter implements RulesInterface
{
    use PaginationTrait;

    public function getRules()
    {
        return array(
            'limit' => $this->getLimitValidation(),
            'page' => $this->getPageValidation(),
            'tag' => array(
                'name' => 'tag',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(["name" => "tag"])
                ]
            ),
            'organisationUnitId' => array(
                'name'       => 'organisationUnitId',
                'required'   => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                ]
            ),
            'orderId' => array(
                'name' => 'orderId',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            )
        );
    }
}