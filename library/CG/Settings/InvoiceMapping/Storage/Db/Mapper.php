<?php
namespace CG\Settings\InvoiceMapping\Storage\Db;

use CG\Settings\InvoiceMapping\Entity as InvoiceMapping;
use CG\Settings\InvoiceMapping\Mapper as InvoiceMappingMapper;

class Mapper extends InvoiceMappingMapper
{
    const DISABLED_DATETIME = '0000-00-00 00:00:00';

    protected $trinaryDateTimeFields = [
        'sendViaEmail',
        'sendToFba',
    ];

    public function getEntityData(InvoiceMapping $entity)
    {
        $data = $entity->toArray(); unset($data['id']);
        foreach ($this->trinaryDateTimeFields as $field) {
            if (isset($data[$field]) && $data[$field] === false) {
                $data[$field] = static::DISABLED_DATETIME;
            }
        }
        return $data;
    }

    public function fromArray(array $entityData)
    {
        foreach ($this->trinaryDateTimeFields as $field) {
            if (isset($entityData[$field]) && $entityData[$field] === static::DISABLED_DATETIME) {
                $entityData[$field] = false;
            }
        }
        return parent::fromArray($entityData);
    }
}
