<?php
use CG\Amazon\Region\Urls;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

class ListingMarketplaceSupport extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'listings';
    }

    protected function getTableNames()
    {
        return ['listing', 'unimportedListing'];
    }

    protected function getMarketplaces()
    {
        return (new Urls())->getUrls();
    }

    public function up()
    {
        foreach ($this->getTableNames() as $tableName) {
            $this
                ->table($tableName)
                ->addColumn('marketplace', 'string', ['null' => true])
                ->update();

            foreach ($this->getMarketplaces() as $marketplace => $url) {
                $this->execute(
                    sprintf('UPDATE %s SET marketplace = "%s" WHERE url LIKE "%s%%"', $tableName, $marketplace, $url)
                );
            }
        }
    }

    public function down()
    {
        foreach ($this->getTableNames() as $tableName) {
            $this
                ->table($tableName)
                ->removeColumn('marketplace')
                ->update();
        }
    }
}
