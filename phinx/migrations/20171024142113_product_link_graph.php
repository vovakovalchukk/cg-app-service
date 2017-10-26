<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Db\Table\ForeignKey;
use Phinx\Db\Adapter\MysqlAdapter;

class ProductLinkGraph extends AbstractMigration
{
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
            ->table('productLinkPath', ['id' => false, 'primary_key' => ['pathId', 'from', 'to'], 'collation' => 'utf8_general_ci'])
            ->addColumn('pathId', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('from', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('to', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('quantity', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('order', 'integer', ['null' => false, 'signed' => false])
            ->addIndex(['pathId', 'order'], ['unique' => true])
            ->addForeignKey('from', 'productLink', 'linkId', ['update' => ForeignKey::CASCADE, 'delete' => ForeignKey::CASCADE])
            ->addForeignKey('to', 'productLink', 'linkId', ['update' => ForeignKey::CASCADE, 'delete' => ForeignKey::CASCADE])
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

                $order = 0;
                $quantity = 1;

                $from = $links[$rootKey];
                foreach ($path as $node) {
                    if (!isset($links[$node['key']])) {
                        break;
                    }

                    $to = $links[$node['key']];
                    $quantity *= $node['quantity'];

                    $this->insertRow('productLinkPath', $pathId, $from, $to, $quantity, $order++);
                    $from = $to;
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
            'SELECT plf.`organisationUnitId`, plf.`sku` as `productSku`, plt.`sku` as `stockSku`, plp.`quantity`'
            . ' FROM productLinkPath plp'
            . ' JOIN productLink plf ON plp.`from` = plf.`linkId`'
            . ' JOIN productLink plt ON plp.`to` = plt.`linkId`'
            . ' WHERE plp.`order` = 0;'
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