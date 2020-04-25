<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\RequestFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RequestFactoryTest extends TestCase
{
    /**
     * @var RequestFactory
     */
    protected $model;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->model = new RequestFactory($this->objectManagerMock);
    }

    /**
     * @covers \Magento\Framework\App\RequestFactory::__construct
     * @covers \Magento\Framework\App\RequestFactory::create
     */
    public function testCreate()
    {
        $arguments = ['some_key' => 'same_value'];

        $appRequest = $this->createMock(RequestInterface::class);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(RequestInterface::class, $arguments)
            ->will($this->returnValue($appRequest));

        $this->assertEquals($appRequest, $this->model->create($arguments));
    }
}
