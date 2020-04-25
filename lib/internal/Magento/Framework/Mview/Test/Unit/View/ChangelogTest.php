<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\Test\Unit\View;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Mview\View\Changelog;
use Magento\Framework\Mview\View\ChangelogInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test Coverage for Changelog View.
 *
 * @see \Magento\Framework\Mview\View\Changelog
 */
class ChangelogTest extends TestCase
{
    /**
     * @var Changelog
     */
    protected $model;

    /**
     * Mysql PDO DB adapter mock
     *
     * @var MockObject|\Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $connectionMock;

    /**
     * @var MockObject|ResourceConnection
     */
    protected $resourceMock;

    protected function setUp(): void
    {
        $this->connectionMock = $this->createMock(Mysql::class);
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->mockGetConnection($this->connectionMock);

        $this->model = new Changelog($this->resourceMock);
    }

    public function testInstanceOf()
    {
        $resourceMock =
            $this->createMock(ResourceConnection::class);
        $resourceMock->expects($this->once())->method('getConnection')->will($this->returnValue(true));
        $model = new Changelog($resourceMock);
        $this->assertInstanceOf(ChangelogInterface::class, $model);
    }

    public function testCheckConnectionException()
    {
        $this->expectException('Magento\Framework\DB\Adapter\ConnectionException');
        $this->expectExceptionMessage('The write connection to the database isn\'t available. Please try again later.');
        $resourceMock =
            $this->createMock(ResourceConnection::class);
        $resourceMock->expects($this->once())->method('getConnection')->will($this->returnValue(null));
        $model = new Changelog($resourceMock);
        $model->setViewId('ViewIdTest');
        $this->assertNull($model);
    }

    public function testGetName()
    {
        $this->model->setViewId('ViewIdTest');
        $this->assertEquals(
            'ViewIdTest' . '_' . Changelog::NAME_SUFFIX,
            $this->model->getName()
        );
    }

    public function testGetViewId()
    {
        $this->model->setViewId('ViewIdTest');
        $this->assertEquals('ViewIdTest', $this->model->getViewId());
    }

    public function testGetNameWithException()
    {
        $this->expectException('DomainException');
        $this->expectExceptionMessage('View\'s identifier is not set');
        $this->model->getName();
    }

    public function testGetColumnName()
    {
        $this->assertEquals(Changelog::COLUMN_NAME, $this->model->getColumnName());
    }

    public function testGetVersion()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, true);
        $this->mockGetTableName();

        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->setMethods(['from', 'order', 'limit'])
            ->getMock();
        $selectMock->expects($this->any())->method('from')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('order')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('limit')->willReturn($selectMock);

        $this->connectionMock->expects($this->any())->method('select')->willReturn($selectMock);

        $this->connectionMock->expects($this->once())
            ->method('fetchRow')
            ->will($this->returnValue(['version_id' => 10]));

        $this->model->setViewId('viewIdtest');
        $this->assertEquals(10, $this->model->getVersion());
    }

    public function testGetVersionEmptyChangelog()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, true);
        $this->mockGetTableName();

        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->setMethods(['from', 'order', 'limit'])
            ->getMock();
        $selectMock->expects($this->any())->method('from')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('order')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('limit')->willReturn($selectMock);

        $this->connectionMock->expects($this->any())->method('select')->willReturn($selectMock);

        $this->connectionMock->expects($this->once())
            ->method('fetchRow')
            ->will($this->returnValue(false));

        $this->model->setViewId('viewIdtest');
        $this->assertEquals(0, $this->model->getVersion());
    }

    public function testGetVersionWithExceptionNoAutoincrement()
    {
        $this->expectException('Magento\Framework\Exception\RuntimeException');
        $this->expectExceptionMessage('Table status for viewIdtest_cl is incorrect. Can`t fetch version id.');
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, true);
        $this->mockGetTableName();

        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->setMethods(['from', 'order', 'limit'])
            ->getMock();
        $selectMock->expects($this->any())->method('from')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('order')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('limit')->willReturn($selectMock);

        $this->connectionMock->expects($this->any())->method('select')->willReturn($selectMock);

        $this->connectionMock->expects($this->once())
            ->method('fetchRow')
            ->will($this->returnValue(['no_version_column' => 'blabla']));

        $this->model->setViewId('viewIdtest');
        $this->model->getVersion();
    }

    public function testGetVersionWithExceptionNoTable()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, false);
        $this->mockGetTableName();

        $this->expectException('Exception');
        $this->expectExceptionMessage("Table {$changelogTableName} does not exist");
        $this->model->setViewId('viewIdtest');
        $this->model->getVersion();
    }

    public function testDrop()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, false);
        $this->mockGetTableName();

        $this->expectException('Exception');
        $this->expectExceptionMessage("Table {$changelogTableName} does not exist");
        $this->model->setViewId('viewIdtest');
        $this->model->drop();
    }

    public function testDropWithException()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, true);
        $this->mockGetTableName();

        $this->connectionMock->expects($this->once())
            ->method('dropTable')
            ->with($changelogTableName)
            ->will($this->returnValue(true));

        $this->model->setViewId('viewIdtest');
        $this->model->drop();
    }

    public function testCreate()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, false);
        $this->mockGetTableName();

        $tableMock = $this->createMock(Table::class);
        $tableMock->expects($this->exactly(2))
            ->method('addColumn')
            ->will($this->returnSelf());

        $this->connectionMock->expects($this->once())
            ->method('newTable')
            ->with($changelogTableName)
            ->will($this->returnValue($tableMock));
        $this->connectionMock->expects($this->once())
            ->method('createTable')
            ->with($tableMock);

        $this->model->setViewId('viewIdtest');
        $this->model->create();
    }

    public function testCreateWithExistingTable()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, true);
        $this->mockGetTableName();

        $this->connectionMock->expects($this->never())->method('createTable');
        $this->model->setViewId('viewIdtest');
        $this->model->create();
    }

    public function testGetList()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, true);
        $this->mockGetTableName();

        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->once())
            ->method('distinct')
            ->with(true)
            ->will($this->returnSelf());
        $selectMock->expects($this->once())
            ->method('from')
            ->with($changelogTableName, ['entity_id'])
            ->will($this->returnSelf());
        $selectMock->expects($this->exactly(2))
            ->method('where')
            ->will($this->returnSelf());

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->will($this->returnValue($selectMock));
        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($selectMock)
            ->will($this->returnValue([1]));

        $this->model->setViewId('viewIdtest');
        $this->assertEquals([1], $this->model->getList(1, 2));
    }

    public function testGetListWithException()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, false);
        $this->mockGetTableName();

        $this->expectException('Exception');
        $this->expectExceptionMessage("Table {$changelogTableName} does not exist");
        $this->model->setViewId('viewIdtest');
        $this->model->getList(random_int(1, 200), random_int(201, 400));
    }

    public function testClearWithException()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, false);
        $this->mockGetTableName();

        $this->expectException('Exception');
        $this->expectExceptionMessage("Table {$changelogTableName} does not exist");
        $this->model->setViewId('viewIdtest');
        $this->model->clear(random_int(1, 200));
    }

    /**
     * @param $connection
     */
    protected function mockGetConnection($connection)
    {
        $this->resourceMock->expects($this->once())->method('getConnection')->will($this->returnValue($connection));
    }

    protected function mockGetTableName()
    {
        $this->resourceMock->expects($this->once())->method('getTableName')->will($this->returnArgument(0));
    }

    /**
     * @param $changelogTableName
     * @param $result
     */
    protected function mockIsTableExists($changelogTableName, $result)
    {
        $this->connectionMock->expects(
            $this->once()
        )->method(
            'isTableExists'
        )->with(
            $this->equalTo($changelogTableName)
        )->will(
            $this->returnValue($result)
        );
    }
}
