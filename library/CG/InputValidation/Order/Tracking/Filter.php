<?php
namespace CG\InputValidation\Order\Tracking;

use CG\Validation\Rules\ValidatorTrait;
use CG\Validation\RulesInterface;
use CG\Validation\Rules\PaginationTrait;

class Filter implements RulesInterface {

    use ValidatorTrait;
    use PaginationTrait;

    public function getRules()
    {
        $rules = $this->getPaginationValidation();
        return $rules;
    }
}