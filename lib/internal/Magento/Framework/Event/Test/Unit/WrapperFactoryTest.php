<?php declare(strict_types=1);
/**
 * @category   Magento
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Event\Test\Unit;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\WrapperFactory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

class WrapperFactoryTest extends TestCase
{
    public function testCreate()
    {
        $expectedInstance = Observer::class;
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);

        $wrapperFactory = new WrapperFactory($objectManagerMock);
        $arguments = ['argument' => 'value', 'data' => 'data'];
        $observerInstanceMock = $this->createMock($expectedInstance);

        $objectManagerMock->expects($this->once())
            ->method('create')
            ->with($expectedInstance, $arguments)
            ->will($this->returnValue($observerInstanceMock));

        $this->assertInstanceOf($expectedInstance, $wrapperFactory->create($arguments));
    }
}
