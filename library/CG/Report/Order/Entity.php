<?php
namespace CG\Report\Order;

use CG\Report\Order\Dataset\Entity as Dataset;

class Entity
{
    /** @var  string */
    protected $name;
    /** @var  string */
    protected $dimension;
    /** @var  Dataset[] */
    protected $datasets;

    public function __construct(string $name, string $dimension, array $values)
    {
        $this->name = $name;
        $this->datasets = $values;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Dataset[]
     */
    public function getDatasets(): array
    {
        return $this->datasets;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'datasets' => $this->datasetsToArray()
        ];
    }

    public function getDimension(): string
    {
        return $this->dimension;
    }

    protected function datasetsToArray()
    {
        $datasets = [];
        foreach ($this->getDatasets() as $dataset) {
            $datasets[] = $dataset->toArray();
        }
        return $datasets;
    }
}
