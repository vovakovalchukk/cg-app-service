<?php
namespace CG\Root;

use CG\Slim\ToHalTrait;
use Zend\Di\Di;

class Mapper
{
    use ToHalTrait;

    protected $di;

    public function __construct(Di $di)
    {
        $this->setDi($di);
    }

    public function getHal()
    {
        return $this->toHal('/')
                    ->addLink('service', '/service');
    }

    public function setDi(Di $di)
    {
        $this->di = $di;
    }

    public function getDi()
    {
        return $this->di;
    }
}