<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit;

use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Element\BlockFactory;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\BlockInterface;
use \Magento\Framework\View\DataSourcePool;

/**
 * Test for view Context model
 */
class DataSourcePoolTest extends TestCase
{
    /**
     * @var DataSourcePool
     */
    protected $dataSourcePool;

    /**
     * @var BlockFactory|MockObject
     */
    protected $blockFactory;

    protected function setUp(): void
    {
        $this->blockFactory = $this->getMockBuilder(BlockFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->dataSourcePool = $objectManager->getObject(
            DataSourcePool::class,
            ['blockFactory' => $this->blockFactory]
        );
    }

    public function testAddWithException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid Data Source class name: NotExistingBlockClass');
        $this->dataSourcePool->add('DataSourcePoolTestBlock', 'NotExistingBlockClass');
    }

    /**
     * @param $blockClass
     * @return MockObject
     */
    protected function createBlock($blockClass)
    {
        $block = $this->createMock(BlockInterface::class);

        $this->blockFactory->expects($this->once())
            ->method('createBlock')
            ->with($blockClass)
            ->will($this->returnValue($block));
        return $block;
    }

    public function testAdd()
    {
        $blockName = 'DataSourcePoolTestBlock';
        $blockClass = DataSourcePoolTestBlock::class;

        $block = $this->createBlock($blockClass);

        $this->assertSame($block, $this->dataSourcePool->add($blockName, $blockClass));
    }

    public function testGet()
    {
        $blockName = 'DataSourcePoolTestBlock';
        $blockClass = DataSourcePoolTestBlock::class;

        $block = $this->createBlock($blockClass);
        $this->dataSourcePool->add($blockName, $blockClass);

        $this->assertSame($block, $this->dataSourcePool->get($blockName));
        $this->assertEquals([$blockName => $block], $this->dataSourcePool->get());
        $this->assertNull($this->dataSourcePool->get('WrongName'));
    }

    public function testGetEmpty()
    {
        $this->assertEquals([], $this->dataSourcePool->get());
    }

    public function testAssignAndGetNamespaceData()
    {
        $blockName = 'DataSourcePoolTestBlock';
        $blockClass = DataSourcePoolTestBlock::class;

        $block = $this->createBlock($blockClass);
        $this->dataSourcePool->add($blockName, $blockClass);

        $namespace = 'namespace';
        $alias = 'alias';
        $this->dataSourcePool->assign($blockName, $namespace, $alias);

        $this->assertEquals(['alias' => $block], $this->dataSourcePool->getNamespaceData($namespace));
        $this->assertEquals([], $this->dataSourcePool->getNamespaceData('WrongNamespace'));
    }
}
