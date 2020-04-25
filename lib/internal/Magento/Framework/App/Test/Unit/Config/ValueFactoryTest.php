<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config;

use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Framework\TestFramework\Unit\AbstractFactoryTestCase;

class ValueFactoryTest extends AbstractFactoryTestCase
{
    protected function setUp(): void
    {
        $this->instanceClassName = ValueInterface::class;
        $this->factoryClassName = ValueFactory::class;
        parent::setUp();
    }

    public function testCreateWithException()
    {
        $this->expectException('InvalidArgumentException');
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue('somethingElse'));
        $this->factory->create();
    }
}
