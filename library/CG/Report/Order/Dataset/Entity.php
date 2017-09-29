<?php

namespace CG\Report\Order\Dataset;

class Entity
{
    /** @var  string */
    protected $date;

    /** @var  array */
    protected $values;

    public function __construct(string $date, array $values)
    {
        $this->date = $date;
        $this->values = $values;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function toArray(): array
    {
        return [
            'date' => $this->getDate(),
            'values' => $this->getValues()
        ];
    }
}
