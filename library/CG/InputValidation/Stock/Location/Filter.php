<?php
namespace CG\InputValidation\Stock\Location;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\RulesInterface;
use CG\Validation\Rules\PaginationTrait;
use Zend\Di\Di;

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
        return array_merge(
            $this->getPaginationValidation(),
            [
                'stockId' => [
                    'name'       => 'stockId',
                    'required'   => false,
                    'validators' => [
                        $this->getDi()->newInstance(ArrayOfIntegersValidator::class, array("name" => "stockId"))
                    ]
                ],
                'locationId' => [
                    'name'       => 'locationId',
                    'required'   => false,
                    'validators' => [
                        $this->getDi()->newInstance(ArrayOfIntegersValidator::class, array("name" => "locationId"))
                    ]
                ],
            ]
        );
    }

    public function setDi($di)
    {
        $this->di = $di;
        return $this;
    }

    public function getDi()
    {
        return $this->di;
    }
}