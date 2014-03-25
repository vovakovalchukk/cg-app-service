<?php
namespace CG\InputValidation\Order\Tracking;

use CG\Validation\Rules\ValidatorTrait;
use CG\Validation\RulesInterface;
use CG\Validation\Rules\PaginationTrait;
use Zend\Validator\Between;
use Zend\Validator\EmailAddress;
use Zend\Validator\NotEmpty;
use Zend\Di\Di;
use CG\Validation\Rules\ArrayOfIntegersValidator;

class Filter implements RulesInterface {

	use ValidatorTrait;
	use PaginationTrait;

	public function __construct(Di $di)
	{
		$this->setDi($di);
	}

	public function getRules()
	{
		$rules = $this->getPaginationValidation();
		return $rules;
	}
}