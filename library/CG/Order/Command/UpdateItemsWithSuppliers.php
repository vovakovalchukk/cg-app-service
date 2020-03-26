<?php
namespace CG\Order\Command;

use CG\Order\Client\Gearman\Generator\UpdateItemsSupplier as UpdateItemsSupplierGearmanJobGenerator;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Db\Sql\Sql;

class UpdateItemsWithSuppliers implements LoggerAwareInterface
{
    use LogTrait;

    /** @var Sql */
    protected $readSql;
    /** @var UpdateItemsSupplierGearmanJobGenerator */
    protected $updateItemsSupplierGearmanJobGenerator;

    public function __construct(Sql $readSql, UpdateItemsSupplierGearmanJobGenerator $updateItemsSupplierGearmanJobGenerator)
    {
        $this->readSql = $readSql;
        $this->updateItemsSupplierGearmanJobGenerator = $updateItemsSupplierGearmanJobGenerator;
    }

    public function __invoke(OutputInterface $output)
    {
        $query = $this->readSql->select('productDetail')
            ->columns(['organisationUnitId', 'sku', 'supplierId'])
            ->where('supplierId IS NOT NULL');

        $results = $this->readSql->prepareStatementForSqlObject($query)->execute();

        $output->writeln('Found ' . $results->getAffectedRows() . ' products with suppliers, generating jobs to update the order items.');

        foreach ($results as  $result) {
            ($this->updateItemsSupplierGearmanJobGenerator)(
                $result['organisationUnitId'],
                $result['sku'],
                $result['supplierId']
            );
        }

        $output->writeln('Successfully generated ' . count($results). ' supplier update jobs.');
    }
}
