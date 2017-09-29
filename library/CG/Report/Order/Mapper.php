<?php
namespace CG\Report\Order;

use CG\Http\Mapper\FromHalTrait;
use CG\Report\Order\Dataset\Entity as Dataset;
use CG\Slim\Mapper\CollectionToHalTrait;
use CG\Stdlib\Mapper\EntityClassInterface;
use CG\Stdlib\Mapper\FromArrayInterface;
use CG\Slim\Renderer\ResponseType\Hal;

class Mapper implements FromArrayInterface, EntityClassInterface
{
    use CollectionToHalTrait;
    use FromHalTrait {
        fromHal as traitFromHal;
    }

    public function getEntityClass()
    {
        return Entity::class;
    }

    public function fromArray(array $entityData): Entity
    {
        $values = [];
        foreach ($entityData['values'] as $dateUnit => $data) {
            $values[] = new Dataset($dateUnit, $data);
        }

        return new ($this->getEntityClass()) (
            $entityData['dimension'],
            $entityData['name'],
            $values
        );
    }

    protected function getEmbeddedResource(): string
    {
        return 'series';
    }

    /**
     * @param Entity $entity
     * @return Hal
     */
    public function toHal($entity)
    {
        $halData = $entity->toArray();
        $hal = new Hal('/report/order/' . $entity->ge(), $halData);

        return $hal;
    }

    protected function getFirstPage(): int
    {
        return 1;
    }
}
