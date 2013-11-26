<?php
namespace CG\InputValidation\Order\Order;

use CG\Validation\RulesInterface;
use Zend\Di\Di;
use Zend\Validator\Between;
use Zend\Validator\Date;
use Zend\Validator\InArray;
use CG\Constant\CountryCode;

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
                    $this->getDi()->newInstance(Between::class, array('options' => array('min' => 1)))
                                  ->setMessages(array('notBetween' => 'limit should be at least %min%'))
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
                    /* array of ints? */
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
                    /* array of ints? */
                )
            ),
            'searchTerm' => array(
                'name'       => 'searchTerm',
                'required'   => false,
                'validators' => array(
                )
            ),
            'status' => array(
                'name'       => 'status',
                'required'   => false,
                'validators' => array(
                    /* List of statuses */
                )
            ),
            'accountId' => array(
                'name'       => 'accountId',
                'required'   => false,
                'validators' => array(
                )
            ),
            'channel' => array(
                'name'       => 'channel',
                'required'   => false,
                'validators' => array(
                    /* List of channels */
                )
            ),
            'includeArchived' => array(
                'name'       => 'includeArchived',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(InArray::class)
                                  ->setHaystack(array(1, "true", 0, "false", "1", "0"))
                )
            ),
            'country' => array(
                'name'       => 'country',
                'required'   => false,
                'validators' => array(
                    //$this->getDi()->newInstance(InArray::class)
                    //              ->setHaystack(CountryCode::getCountryCodes())
                    /* array of of haystacks? */
                )
            ),
            'countryExclude' => array(
                'name'       => 'countryExclude',
                'required'   => false,
                'validators' => array(
                    //$this->getDi()->newInstance(InArray::class)
                    //              ->setHaystack(CountryCode::getCountryCodes())
                    /* array of haystacks? */
                )
            ),
            'multiLineOrder' => array(
                'name'       => 'multiLineOrder',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(InArray::class)
                                  ->setHaystack(array(1, "true", 0, "false", "1", "0"))
                )
            ),
            'multiSameItem' => array(
                'name'       => 'multiSameItem',
                'required'   => false,
                'validators' => array(
                    $this->getDi()->newInstance(InArray::class)
                                  ->setHaystack(array(1, "true", 0, "false", "1", "0"))
                )
            ),
            'shippingMethod' => array(
                'name'       => 'shippingMethod',
                'required'   => false,
                'validators' => array(
                )
            ),
            'orderBy' => array(
                'name'       => 'orderBy',
                'required'   => false,
                'validators' => array(
                    /* list of fields */
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
        );
    }
}