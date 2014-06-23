<?php
namespace CG\InputValidation\Settings\Invoice;

use CG\Validation\RulesInterface;
use Zend\Di\Di;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\PaginationTrait;

class Filter implements RulesInterface
{
    use PaginationTrait;

    protected $di;

    public function __construct(Di $di)
    {
        $this->setDi($di);
    }

    public function getRules()
    {
        $rules = $this->getPaginationValidation();
        $rules['id'] = [
            'name'       => 'id',
            'required'   => false,
            'validators' => [
                $this->getDi()->newInstance(IsArrayValidator::class, ['name' => 'id'])
            ]
        ];
        return $rules;
    }

    protected function getDi()
    {
        return $this->di;
    }

    protected function setDi(Di $di)
    {
        $this->di = $di;
    }
}