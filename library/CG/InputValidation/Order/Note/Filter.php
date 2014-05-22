<?php
namespace CG\InputValidation\Order\Note;

use CG\Validation\Rules\ValidatorTrait;
use CG\Validation\RulesInterface;
use CG\Validation\Rules\PaginationTrait;
use Zend\Di\Di;


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