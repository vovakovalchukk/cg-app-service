<?php
namespace CG\InputValidation\RestExample;

use Zend\Di\Di;

use Zend\Validator\Between;

class Filter
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
            'status' => array(
                'name'       => 'status',
                'required'   => true,
                'validators' => array(
                    $this->getDi()->newInstance(
                        Between::class,
                        array(
                            'options' => array(
                                'min' => 1,
                                'max' => 600
                            )
                        )
                    )->setMessages(
                        array(
                            'notBetween' => 'status should be between %min% & %max%',
                        )
                    )
                )
            )
        );
    }
}