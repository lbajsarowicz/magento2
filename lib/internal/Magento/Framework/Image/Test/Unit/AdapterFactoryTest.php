<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Image\Test\Unit;

use Magento\Framework\Image\Adapter\ConfigInterface;
use Magento\Framework\Image\Adapter\Gd2;
use Magento\Framework\Image\Adapter\ImageMagick;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\ObjectManager\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AdapterFactoryTest extends TestCase
{
    /**
     * @var ConfigInterface|MockObject
     */
    protected $configMock;

    protected function setUp(): void
    {
        $this->configMock = $this->createPartialMock(
            ConfigInterface::class,
            ['getAdapterAlias', 'getAdapters']
        );

        $this->configMock->expects(
            $this->once()
        )->method(
            'getAdapters'
        )->will(
            $this->returnValue(
                [
                    'GD2' => ['class' => Gd2::class],
                    'IMAGEMAGICK' => ['class' => ImageMagick::class],
                    'wrongInstance' => ['class' => 'stdClass'],
                    'test' => [],
                ]
            )
        );
    }

    /**
     * @dataProvider createDataProvider
     * @param string $alias
     * @param string $class
     */
    public function testCreate($alias, $class)
    {
        $objectManagerMock =
            $this->createPartialMock(ObjectManager::class, ['create']);
        $imageAdapterMock = $this->createPartialMock($class, ['checkDependencies']);
        $imageAdapterMock->expects($this->once())->method('checkDependencies');

        $objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $class
        )->will(
            $this->returnValue($imageAdapterMock)
        );

        $adapterFactory = new AdapterFactory($objectManagerMock, $this->configMock);
        $imageAdapter = $adapterFactory->create($alias);
        $this->assertInstanceOf($class, $imageAdapter);
    }

    /**
     * @see self::testCreate()
     * @return array
     */
    public function createDataProvider()
    {
        return [
            ['GD2', Gd2::class],
            ['IMAGEMAGICK', ImageMagick::class]
        ];
    }

    /**
     * @covers \Magento\Framework\Image\AdapterFactory::create
     */
    public function testCreateWithoutName()
    {
        $adapterAlias = 'IMAGEMAGICK';
        $adapterClass = ImageMagick::class;

        $this->configMock->expects($this->once())->method('getAdapterAlias')->will($this->returnValue($adapterAlias));

        $objectManagerMock =
            $this->createPartialMock(ObjectManager::class, ['create']);
        $imageAdapterMock = $this->createPartialMock($adapterClass, ['checkDependencies']);
        $imageAdapterMock->expects($this->once())->method('checkDependencies');

        $objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $adapterClass
        )->will(
            $this->returnValue($imageAdapterMock)
        );

        $adapterFactory = new AdapterFactory($objectManagerMock, $this->configMock);
        $imageAdapter = $adapterFactory->create();
        $this->assertInstanceOf($adapterClass, $imageAdapter);
    }

    /**
     * @covers \Magento\Framework\Image\AdapterFactory::create
     */
    public function testInvalidArgumentException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Image adapter is not selected.');
        $this->configMock->expects($this->once())->method('getAdapterAlias')->will($this->returnValue(''));
        $objectManagerMock =
            $this->createPartialMock(ObjectManager::class, ['create']);
        $adapterFactory = new AdapterFactory($objectManagerMock, $this->configMock);
        $adapterFactory->create();
    }

    /**
     * @covers \Magento\Framework\Image\AdapterFactory::create
     */
    public function testNonAdapterClass()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Image adapter for \'test\' is not setup.');
        $alias = 'test';
        $objectManagerMock =
            $this->createPartialMock(ObjectManager::class, ['create']);

        $adapterFactory = new AdapterFactory($objectManagerMock, $this->configMock);
        $adapterFactory->create($alias);
    }

    /**
     * @covers \Magento\Framework\Image\AdapterFactory::create
     */
    public function testWrongInstance()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('stdClass is not instance of \Magento\Framework\Image\Adapter\AdapterInterface');
        $alias = 'wrongInstance';
        $class = 'stdClass';
        $objectManagerMock =
            $this->createPartialMock(ObjectManager::class, ['create']);
        $imageAdapterMock = $this->createPartialMock($class, ['checkDependencies']);

        $objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $class
        )->will(
            $this->returnValue($imageAdapterMock)
        );

        $adapterFactory = new AdapterFactory($objectManagerMock, $this->configMock);
        $adapterFactory->create($alias);
    }
}
