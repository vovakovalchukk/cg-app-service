<?php
namespace CG\InputValidation\OrderCounts;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\RulesInterface;
use CG\Validation\ExclusionInterface;
use Zend\Validator\Between;
use Zend\Validator\Date;
use Zend\Validator\InArray;
use Zend\Validator\Identical;
use CG\Validation\Rules\BooleanValidator;
use CG\Constant\CountryCode;
use CG\Constant\CurrencyCode;
use CG\Validation\Rules\InArrayValidator;
use CG\Validation\Rules\IsArrayValidator;
use Zend\Validator\StringLength;
use CG\Validation\ValidatorChain;
use CG\Validation\Rules\DecimalValidator;
use CG\Validation\InputValidator;
use CG\Validation\Rules\IntegerValidator;

class Filter implements RulesInterface
{
    use PaginationTrait;

    public function getRules()
    {
        return [
            'limit' => $this->getLimitValidation(),
            'page' => $this->getPageValidation(),
        ];
    }
}
