<?php
namespace CG\Stock\Location;

class TypedEntity extends Entity
{
    const TYPE_REAL = 'real';
    const TYPE_LINKED = 'linked';

    /** @var string $type */
    protected $type = self::TYPE_REAL;

    public function toArray()
    {
        $array = parent::toArray();
        $array['type'] = $this->getType();
        return $array;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}