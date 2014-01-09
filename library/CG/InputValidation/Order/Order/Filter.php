<?php
namespace CG\InputValidation\Order\Order;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\RulesInterface;
use Zend\Di\Di;
use Zend\Validator\Between;
use Zend\Validator\Date;
use Zend\Validator\InArray;
use Zend\Validator\Identical;
use CG\Validation\Rules\BooleanValidator;
use CG\Constant\CountryCode;
use CG\Validation\Rules\InArrayValidator;
use CG\Validation\Rules\IsArrayValidator;
use Zend\Validator\StringLength;
use CG\Validation\ValidatorChain;

class Filter implements RulesInterface
{
    protected $di;

    public function __construct(Di $di)
    {
        $this->setDi($di);
    }

    protected function getDi()
    {
        return $this->di;
    }

    protected function setDi(Di $di)
    {
        $this->di = $di;
    }

    public function getRules()
    {
        return array(
            'limit' => array(
                'name'       => 'limit',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(
                        ValidatorChain::Class,
                        [
                            'validators' => [
                                $this->getDi()->newInstance(Between::class, array('options' => array('min' => 1)))
                                    ->setMessages(array('notBetween' => 'limit should be at least %min%')),
                                $this->getDi()->newInstance(Identical::Class, ['token' => 'all'])
                                    ->setMessages([Identical::NOT_SAME => 'limit does not equal "%token%"'])
                            ]
                        ]
                    )
                )
            ),
            'page' => array(
                'name'       => 'page',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(Between::class, array('options' => array('min' => 1)))
                                  ->setMessages(array('notBetween' => 'page should be at least %min%'))
                )
            ),
            'id' => array(
                'name'       => 'id',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(ArrayOfIntegersValidator::class, array("name" => "id"))
                ),
            ),
            'timeFrom' => array(
                'name'       => 'timeFrom',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(Date::class, array('options' => array('format' => "Y-m-d H:i:s")))
                )
            ),
            'timeTo' => array(
                'name'       => 'timeTo',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(Date::class, array('options' => array('format' => "Y-m-d H:i:s")))
                )
            ),
            'organisationUnitId' => array(
                'name'       => 'organisationUnitId',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(ArrayOfIntegersValidator::class, array("name" => "organisationUnitId"))
                )
            ),
            'searchTerm' => array(
                'name'       => 'searchTerm',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                )
            ),
            'status' => array(
                'name'       => 'status',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, array('name' => 'status'))
                )
            ),
            'accountId' => array(
                'name'       => 'accountId',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(ArrayOfIntegersValidator::class, array("name" => "accountId"))
                )
            ),
            'channel' => array(
                'name'       => 'channel',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, array('name' => 'channel'))
                )
            ),
            'includeArchived' => array(
                'name'       => 'includeArchived',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(BooleanValidator::class, ['options' => ['name' => 'multiSameItem']])
                )
            ),
            'country' => array(
                'name'       => 'country',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, array('name' => 'country')),
                    $this->getDi()->newInstance(InArrayValidator::class, array('name' => 'country',
                        'haystack' => CountryCode::getCountryCodes()))
                )
            ),
            'countryExclude' => array(
                'name'       => 'countryExclude',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, array('name' => 'countryExclude')),
                    $this->getDi()->newInstance(InArrayValidator::class, array('name' => 'countryExclude',
                        'haystack' => CountryCode::getCountryCodes()))
                )
            ),
            'multiLineOrder' => array(
                'name'       => 'multiLineOrder',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(BooleanValidator::class, ['options' => ['name' => 'multiLineOrder']])
                )
            ),
            'multiSameItem' => array(
                'name'       => 'multiSameItem',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(BooleanValidator::class, ['options' => ['name' => 'multiSameItem']])
                )
            ),
            'shippingMethod' => array(
                'name'       => 'shippingMethod',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, array('name' => 'shippingMethod'))
                )
            ),
            'orderBy' => array(
                'name'       => 'orderBy',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(StringLength::class, ['options' => ['min' => 1]])
                )
            ),
            'orderDirection' => array(
                'name'       => 'orderDirection',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(InArray::class)
                                  ->setHaystack(array("ASC", "DESC"))
                )
            ),
            'tags' => array(
                'name'       => 'tags',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(IsArrayValidator::class, array('name' => 'tags'))
                )
            )
        );
    }
}