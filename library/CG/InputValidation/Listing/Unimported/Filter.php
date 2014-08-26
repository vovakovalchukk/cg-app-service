<?php
namespace CG\InputValidation\Listing\Unimported;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\RulesInterface;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\ValidatorChain;
use Zend\Validator\Between;
use Zend\Validator\GreaterThan;
use Zend\Validator\Identical;
use Zend\Validator\StringLength;
use Zend\Di\Di;

class Filter implements RulesInterface
{
    protected $di;

    use PaginationTrait;

    public function __construct(Di $di)
    {
        $this->setDi($di);
    }

    public function getRules()
    {
        return [
            'limit' => $this->getLimitValidation(),
            'page' => $this->getPageValidation(),
            'id' => [
                'name'       => 'id',
                'required'   => false,
                'validators' => [
                    $this->getDi()->newInstance(ArrayOfIntegersValidator::class, ["name" => "id"])
                ]
            ],
            'organisationUnitId' => [
                'name'       => 'organisationUnitId',
                'required'   => false,
                'validators' => [
                    $this->getDi()->newInstance(ArrayOfIntegersValidator::class, ["name" => "organisationUnitId"])
                ]
            ],
            'accountId' => [
                'name' => 'accountId',
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(ArrayOfIntegersValidator::class, ["name" => "accountId"])
                ]
            ],
            'externalId' => [
                'name' => 'externalId',
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(IsArrayValidator::class, ["name" => "externalId"])
                ]
            ],
            'sku' => [
                'name' => 'sku',
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(IsArrayValidator::class, ["name" => "sku"])
                ]
            ],
            'title' => [
                'name' => 'title',
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(IsArrayValidator::class, ["name" => "title"])
                ]
            ],
            'url' => [
                'name' => 'url',
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(IsArrayValidator::class, ["name" => "url"])
                ]
            ],
            'imageId' => [
                'name' => 'imageId',
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(ArrayOfIntegersValidator::class, ["name" => "imageId"])
                ]
            ],
            'createdDate' => [
                'name' => 'createdDate',
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(IsArrayValidator::class, ["name" => "createdDate"])
                ]
            ],
            'status' => [
                'name' => 'status',
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(IsArrayValidator::class, ["name" => "status"])
                ]
            ],
            'variationCount' => [
                'name' => 'variationCount',
                'required' => false,
                'validators' => [
                    $this->getDi()->newInstance(IsArrayValidator::class, ["name" => "variationCount"])
                ]
            ]
        ];
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