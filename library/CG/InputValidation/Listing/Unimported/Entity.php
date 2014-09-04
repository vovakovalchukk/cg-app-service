<?php
namespace CG\InputValidation\Listing\Unimported;

use CG\Validation\Rules\IntegerValidator;
use CG\Validation\RulesInterface;
use Zend\Validator\GreaterThan;
use Zend\Validator\StringLength;
use Zend\Validator\Date;
use CG\Validation\Rules\IsArrayValidator;
use Zend\Di\Di;
use Zend\Validator\Uri;

class Entity implements RulesInterface
{
    protected $di;

    public function __construct(Di $di)
    {
        $this->setDi($di);
    }

    public function getRules()
    {
        return [
            'id' => [
                'name'       => 'id',
                'required'   => false,
                'validators' => []
             ],
            'organisationUnitId' => [
                'name'       => 'organisationUnitId',
                'required'   => true,
                'validators' => [
                    $this->getDi()->newInstance(IntegerValidator::class, ['name' => 'organisationUnitId']),
                    $this->getDi()->newInstance(GreaterThan::class, ['options' => ['min' => 1, 'inclusive' => true]])
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ],
            'accountId' => [
                'name'       => 'accountId',
                'required'   => true,
                'validators' => [
                    $this->getDi()->newInstance(IntegerValidator::class, ["name" => "accountId"])
                ]
            ],
            'externalId' => [
                'name'       => 'externalId',
                'required'   => true,
                'validators' => [$this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])]
            ],
            'sku' => [
                'name'       => 'sku',
                'required'   => true,
                'validators' => [$this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])]
            ],
            'title' => [
                'name'       => 'title',
                'required'   => true,
                'validators' => [$this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])]
            ],
            'url' => [
                'name'       => 'url',
                'required'   => true,
                'validators' => [$this->getDi()->newInstance(Uri::class, ['name' => 'url'])]
            ],
            'imageId' => [
                'name'       => 'imageId',
                'required'   => false,
                'validators' => [$this->getDi()->newInstance(IntegerValidator::class, ['name' => 'imageId'])]
            ],
            'createdDate' => [
                'name'       => 'createdDate',
                'required'   => true,
                'validators' => [$this->getDi()->newInstance(Date::class, ['options' => ['format' => 'Y-m-d H:i:s']])]
            ],
            'status' => [
                'name'       => 'status',
                'required'   => true,
                'validators' => [$this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])]
            ],
            'variationCount' => [
                'name'       => 'variationCount',
                'required'   => true,
                'validators' => [$this->getDi()->newInstance(IntegerValidator::class, ['name' => 'variationCount'])]
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