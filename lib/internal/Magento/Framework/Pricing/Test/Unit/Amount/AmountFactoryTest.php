<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Test\Unit\Amount;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\Amount\AmountFactory;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Amount\Base;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AmountFactoryTest extends TestCase
{
    /**
     * @var AmountFactory
     */
    protected $factory;

    /**
     * @var ObjectManager|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Base|MockObject
     */
    protected $amountMock;

    /**
     * Test setUp
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(ObjectManager::class);
        $this->amountMock = $this->createMock(Base::class);
        $this->factory = new AmountFactory($this->objectManagerMock);
    }

    /**
     * Test method create
     */
    public function testCreate()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo(AmountInterface::class),
                $this->equalTo(
                    [
                        'amount' => 'this-is-float-amount',
                        'adjustmentAmounts' => ['this-is-array-of-adjustments'],
                    ]
                )
            )
            ->will($this->returnValue($this->amountMock));
        $this->assertEquals(
            $this->amountMock,
            $this->factory->create('this-is-float-amount', ['this-is-array-of-adjustments'])
        );
    }

    /**
     * Test method create
     */
    public function testCreateException()
    {
        $this->expectException('InvalidArgumentException');
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo(AmountInterface::class),
                $this->equalTo(
                    [
                        'amount' => 'this-is-float-amount',
                        'adjustmentAmounts' => ['this-is-array-of-adjustments'],
                    ]
                )
            )
            ->will($this->returnValue(new \stdClass()));
        $this->assertEquals(
            $this->amountMock,
            $this->factory->create('this-is-float-amount', ['this-is-array-of-adjustments'])
        );
    }
}
