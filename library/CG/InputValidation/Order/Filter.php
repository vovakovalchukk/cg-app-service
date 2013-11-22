<?php
namespace CG\InputValidation\Order;

use CG\Validation\RulesInterface;
use Zend\Di\Di;
use Zend\Validator\Between;
use Zend\Validator\Date;
use Zend\Validator\InArray;

class Filter implements RulesInterface
{
    protected $di;
    private $countryCodes = array(
        "AF",
        "AX",
        "AL",
        "DZ",
        "AS",
        "AD",
        "AO",
        "AI",
        "AQ",
        "AG",
        "AR",
        "AM",
        "AW",
        "AU",
        "AT",
        "AZ",
        "BS",
        "BH",
        "BD",
        "BB",
        "BY",
        "BE",
        "BZ",
        "BJ",
        "BM",
        "BT",
        "BO",
        "BA",
        "BW",
        "BV",
        "BR",
        "IO",
        "BN",
        "BG",
        "BF",
        "BI",
        "KH",
        "CM",
        "CA",
        "CV",
        "KY",
        "CF",
        "TD",
        "CL",
        "CN",
        "CX",
        "CC",
        "CO",
        "KM",
        "CG",
        "CD",
        "CK",
        "CR",
        "CI",
        "HR",
        "CU",
        "CY",
        "CZ",
        "DK",
        "DJ",
        "DM",
        "DO",
        "EC",
        "EG",
        "SV",
        "GQ",
        "ER",
        "EE",
        "ET",
        "FK",
        "FO",
        "FJ",
        "FI",
        "FR",
        "GF",
        "PF",
        "TF",
        "GA",
        "GM",
        "GE",
        "DE",
        "GH",
        "GI",
        "GR",
        "GL",
        "GD",
        "GP",
        "GU",
        "GT",
        "GG",
        "GN",
        "GW",
        "GY",
        "HT",
        "HM",
        "VA",
        "HN",
        "HK",
        "HU",
        "IS",
        "IN",
        "ID",
        "IR",
        "IQ",
        "IE",
        "IM",
        "IL",
        "IT",
        "JM",
        "JP",
        "JE",
        "JO",
        "KZ",
        "KE",
        "KI",
        "KR",
        "KW",
        "KG",
        "LA",
        "LV",
        "LB",
        "LS",
        "LR",
        "LY",
        "LI",
        "LT",
        "LU",
        "MO",
        "MK",
        "MG",
        "MW",
        "MY",
        "MV",
        "ML",
        "MT",
        "MH",
        "MQ",
        "MR",
        "MU",
        "YT",
        "MX",
        "FM",
        "MD",
        "MC",
        "MN",
        "ME",
        "MS",
        "MA",
        "MZ",
        "MM",
        "NA",
        "NR",
        "NP",
        "NL",
        "AN",
        "NC",
        "NZ",
        "NI",
        "NE",
        "NG",
        "NU",
        "NF",
        "MP",
        "NO",
        "OM",
        "PK",
        "PW",
        "PS",
        "PA",
        "PG",
        "PY",
        "PE",
        "PH",
        "PN",
        "PL",
        "PT",
        "PR",
        "QA",
        "RE",
        "RO",
        "RU",
        "RW",
        "BL",
        "SH",
        "KN",
        "LC",
        "MF",
        "PM",
        "VC",
        "WS",
        "SM",
        "ST",
        "SA",
        "SN",
        "RS",
        "SC",
        "SL",
        "SG",
        "SK",
        "SI",
        "SB",
        "SO",
        "ZA",
        "GS",
        "ES",
        "LK",
        "SD",
        "SR",
        "SJ",
        "SZ",
        "SE",
        "CH",
        "SY",
        "TW",
        "TJ",
        "TZ",
        "TH",
        "TL",
        "TG",
        "TK",
        "TO",
        "TT",
        "TN",
        "TR",
        "TM",
        "TC",
        "TV",
        "UG",
        "UA",
        "AE",
        "GB",
        "US",
        "UM",
        "UY",
        "UZ",
        "VU",
        "VE",
        "VN",
        "VG",
        "VI",
        "WF",
        "EH",
        "YE",
        "ZM",
        "ZW"
    );

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
                    //              ->setHaystack($this->getCountryCodes())
                    /* array of of haystacks? */
                )
            ),
            'countryExclude' => array(
                'name'       => 'countryExclude',
                'required'   => false,
                'validators' => array(
                    //$this->getDi()->newInstance(InArray::class)
                    //              ->setHaystack($this->getCountryCodes())
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

    protected function getCountryCodes()
    {
        return $this->countryCodes;
    }
}