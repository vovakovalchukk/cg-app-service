<?php
namespace CG\Product\ProductFilter\Storage;

use CG\Product\ProductFilter\Filter;
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
            ->setMethods(['fetchEntityCount', 'getSelect', 'getReadSql', 'fetchCollection', 'getEntityClass', 'getMapper', 'getDefaultFilterForUser', 'getDefaultFilterForOrg'])
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
        $filter->expects($this->once())
            ->method('getUserId')
            ->willReturn(42);
        $filter->expects($this->once())
            ->method('getOrganisationUnitId')
            ->willReturn(42);
        $this->db->expects($this->once())
            ->method('getDefaultFilterForUser');
        $this->db->fetchCollectionByFilter($filter);
    }

    public function testFetchCollectionByFilterOrg()
    {
        $this->expectException(NotFound::class);
        $filter = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filter->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $filter->expects($this->once())
            ->method('getUserId')
            ->willReturn(null);
        $filter->expects($this->once())
            ->method('getOrganisationUnitId')
            ->willReturn(42);
        $this->db->expects($this->once())
            ->method('getDefaultFilterForOrg');
        $this->db->fetchCollectionByFilter($filter);
    }

    public function fetchCollectionByFilterGenericDataProvider(): array
    {
        return [
            'id set' => [42, 42, 42],
            'id set 2' => [42, null, 42],
            'id set 3' => [42, 42, null],
            'no id, but no org id' => [null, 42, null],
        ];
    }

    /**
     * @dataProvider fetchCollectionByFilterGenericDataProvider
     * @param ?int $id
     * @param ?int $userId
     * @param ?int $orgId
     * @return void
     */
    public function testFetchCollectionByFilterGeneric(?int $id, ?int $userId, ?int $orgId)
    {
        $this->expectException(NotFound::class);
        $filter = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filter->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($id);
        $filter->expects($this->atLeastOnce())
            ->method('getUserId')
            ->willReturn($userId);
        $filter->expects($this->atLeastOnce())
            ->method('getOrganisationUnitId')
            ->willReturn($orgId);
        $this->db->expects($this->never())
            ->method('getDefaultFilterForUser');
        $this->db->expects($this->never())
            ->method('getDefaultFilterForOrg');
        $this->db->fetchCollectionByFilter($filter);
    }
}
