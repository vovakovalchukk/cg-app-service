<?php
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Db\Table\ForeignKey;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductLinkGraph extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        $rootKeys = [];
        $productLinkTree = [];

        foreach ($this->query('SELECT `organisationUnitId`, `productSku`, `stockSku`, `quantity` FROM productLink') as $productLink) {
            $parentKey = $productLink['organisationUnitId'] . '-' . $productLink['productSku'];
            if (!isset($productLinkTree[$parentKey])) {
                $rootKeys[$parentKey] = true;
                $productLinkTree[$parentKey] = [
                    'organisationUnitId' => $productLink['organisationUnitId'],
                    'sku' => $productLink['productSku'],
                    'quantity' => [],
                    'children' => [],
                ];
            }

            $rootKey = $productLink['organisationUnitId'] . '-' . $productLink['stockSku'];
            if (!isset($productLinkTree[$rootKey])) {
                unset($rootKeys[$rootKey]);
                $productLinkTree[$rootKey] = [
                    'organisationUnitId' => $productLink['organisationUnitId'],
                    'sku' => $productLink['stockSku'],
                    'quantity' => [],
                    'children' => [],
                ];
            }

            $productLinkTree[$rootKey]['quantity'][$parentKey] = $productLink['quantity'];
            $productLinkTree[$parentKey]['children'][$rootKey] = &$productLinkTree[$rootKey];
        }

        $this->table('productLink')->drop();
        $this
            ->table('productLink', ['id' => false, 'primary_key' => 'linkId', 'collation' => 'utf8_general_ci'])
            ->addColumn('linkId', 'integer', ['autoIncrement' => true, 'null' => false, 'signed' => false])
            ->addColumn('organisationUnitId', 'integer', ['null' => false])
            ->addColumn('sku', 'string', ['null' => false])
            ->addIndex(['organisationUnitId', 'sku'], ['unique' => true])
            ->create();
        $this
            ->table('productLinkPath', ['id' => false, 'primary_key' => ['pathId', 'order'], 'collation' => 'utf8_general_ci'])
            ->addColumn('pathId', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('linkId', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('quantity', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('order', 'integer', ['null' => false, 'signed' => false])
            ->addIndex(['linkId', 'order'])
            ->addForeignKey('linkId', 'productLink', 'linkId', ['update' => ForeignKey::CASCADE, 'delete' => ForeignKey::RESTRICT])
            ->create();

        $links = [];
        foreach ($productLinkTree as $key => $productLink) {
            $links[$key] = $this->insertRow('productLink', null, $productLink['organisationUnitId'], $productLink['sku']);
        }

        $pathId = 1;
        foreach ($productLinkTree as $key => $productLink) {
            foreach ($this->getPath($key, $productLink) as $rootKey => $path) {
                if (!isset($links[$rootKey]) || empty($path)) {
                    continue;
                }

                $this->insertRow('productLinkPath', $pathId, $links[$rootKey], ($quantity = 1), ($order = 0));
                foreach ($path as $node) {
                    if (!isset($links[$node['key']])) {
                        break;
                    }

                    $quantity *= $node['quantity'];
                    $this->insertRow('productLinkPath', $pathId, $links[$node['key']], $quantity, ++$order);
                }
                $pathId++;
            }
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $productLinks = $this->query(
            'SELECT DISTINCT parent.organisationUnitId, parent.sku as productSku, child.`sku` as stockSku, `to`.quantity'
            . ' FROM productLink parent'
            . ' JOIN productLinkPath `from` ON parent.linkId = `from`.linkId AND `from`.order = 0'
            . ' JOIN productLinkPath `to` ON `from`.pathId = `to`.pathId AND (`from`.order + 1) = `to`.order'
            . ' JOIN productLink child ON `to`.linkId = child.linkId'
        );

        $this->table('productLinkPath')->drop();
        $this->table('productLink')->drop();
        $this
            ->table('productLink', ['id' => false, 'primary_key' => ['organisationUnitId', 'productSku', 'stockSku'], 'collation' => 'utf8_general_ci'])
            ->addColumn('organisationUnitId', 'integer', ['null' => false])
            ->addColumn('productSku', 'string', ['null' => false])
            ->addColumn('stockSku', 'string', ['null' => false])
            ->addColumn('quantity', 'integer', ['null' => false, 'signed' => false])
            ->create();

        foreach ($productLinks as $productLink) {
            $this->insertRow(
                'productLink',
                $productLink['organisationUnitId'],
                $productLink['productSku'],
                $productLink['stockSku'],
                $productLink['quantity']
            );
        }
    }

    protected function getPath($key, ?array $productLinkTree)
    {
        if ($productLinkTree === null || empty($productLinkTree['children'])) {
            yield $key => [];
            return;
        }

        foreach ($productLinkTree['children'] as $childKey => $childTree) {
            foreach ($this->getPath($childKey, $childTree) as $fullPath) {
                array_unshift($fullPath, ['key' => $childKey, 'quantity' => $childTree['quantity'][$key]]);
                yield $key => $fullPath;
            }
        }
    }

    protected function insertRow($table, ...$values)
    {
        /** @var MysqlAdapter $adapter */
        $adapter = $this->getAdapter();
        $insert = $adapter->getConnection()->prepare(
            sprintf('INSERT INTO `%s` VALUES(%s)', $table, implode(',', array_fill(0, count($values), '?')))
        );

        try {
            if ($insert->execute($values)) {
                return $adapter->getConnection()->lastInsertId();
            }
            return null;
        } finally {
            $insert->closeCursor();
        }
    }
}