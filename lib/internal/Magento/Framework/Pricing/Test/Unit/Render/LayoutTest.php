<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Pricing\Test\Unit\Render;

use Magento\Framework\Pricing\Render\Layout;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Framework\Pricing\Render\Layout
 */
class LayoutTest extends TestCase
{
    /**
     * @var Layout
     */
    protected $model;

    /**
     * @var  LayoutInterface|MockObject
     */
    protected $layout;

    /**
     * @var LayoutFactory|MockObject
     */
    protected $layoutFactory;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $generalLayout;

    protected function setUp(): void
    {
        $this->layout = $this->createMock(LayoutInterface::class);
        $this->generalLayout = $this->createMock(LayoutInterface::class);

        $isCacheable = false;
        $this->generalLayout->expects($this->once())
            ->method('isCacheable')
            ->will($this->returnValue(false));
        $layoutFactory = $this->getMockBuilder(LayoutFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $layoutFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo(['cacheable' => $isCacheable]))
            ->will($this->returnValue($this->layout));

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Layout::class,
            [
                'layoutFactory' => $layoutFactory,
                'generalLayout' => $this->generalLayout
            ]
        );
    }

    public function testAddHandle()
    {
        $handle = 'test_handle';

        $layoutProcessor = $this->createMock(ProcessorInterface::class);
        $layoutProcessor->expects($this->once())
            ->method('addHandle')
            ->with($handle);
        $this->layout->expects($this->once())
            ->method('getUpdate')
            ->will($this->returnValue($layoutProcessor));

        $this->model->addHandle($handle);
    }

    public function testLoadLayout()
    {
        $layoutProcessor = $this->createMock(ProcessorInterface::class);
        $layoutProcessor->expects($this->once())
            ->method('load');
        $this->layout->expects($this->once())
            ->method('getUpdate')
            ->will($this->returnValue($layoutProcessor));

        $this->layout->expects($this->once())
            ->method('generateXml');

        $this->layout->expects($this->once())
            ->method('generateElements');

        $this->model->loadLayout();
    }

    public function testGetBlock()
    {
        $blockName = 'block.name';

        $block = $this->createMock(BlockInterface::class);

        $this->layout->expects($this->once())
            ->method('getBlock')
            ->with($blockName)
            ->will($this->returnValue($block));

        $this->assertEquals($block, $this->model->getBlock($blockName));
    }
}
