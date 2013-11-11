<?php
namespace CG\RestExample;

class Entity
{
    protected $id;
    protected $test;

    public function __construct($id, $test)
    {
        $this->setId($id)->setTest($test);
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTest($test)
    {
        $this->test = $test;
        return $this;
    }

    public function getTest()
    {
        return $this->test;
    }

    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'test' => $this->getTest()
        );
    }
}
