<?php
namespace CG\Product\ProductSort\Storage;

use CG\Product\ProductSort\Filter;
use CG\Stdlib\Exception\Runtime\NotFound;
use PHPUnit\Framework\TestCase;

class DbTest extends TestCase
{
    protected $db;

    protected function setUp()
    {
        parent::setUp();
        $this->db = $this->getMockBuilder(Db::class)
            ->disableOriginalConstructor()
            ->setMethods(['fetchEntityCount', 'getSelect', 'getReadSql', 'fetchCollection', 'getEntityClass', 'getMapper', 'getDefaultFilterForUser'])
            ->getMock();
    }

    public function testFetchCollectionByFilterUser()
    {
        $this->expectException(NotFound::class);
        $filter = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filter->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->db->expects($this->once())
            ->method('getDefaultFilterForUser');
        $this->db->fetchCollectionByFilter($filter);
    }

    public function testFetchCollectionByFilterGeneric()
    {
        $this->expectException(NotFound::class);
        $filter = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filter->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(42);
        $this->db->expects($this->never())
            ->method('getDefaultFilterForUser');
        $this->db->fetchCollectionByFilter($filter);
    }
}
