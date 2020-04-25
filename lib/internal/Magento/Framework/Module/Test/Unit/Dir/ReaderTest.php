<?php declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\Module\Dir\File
 */
namespace Magento\Framework\Module\Test\Unit\Dir;

use Magento\Framework\App\Config\Base;
use Magento\Framework\App\Config\BaseFactory;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Module\ModuleListInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * @var Reader
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_moduleListMock;

    /**
     * @var MockObject
     */
    protected $_protFactoryMock;

    /**
     * @var MockObject
     */
    protected $_dirsMock;

    /**
     * @var MockObject
     */
    protected $_baseConfigMock;

    /**
     * @var MockObject
     */
    protected $_fileIteratorFactory;

    /**
     * @var MockObject
     */
    protected $directoryReadFactoryMock;

    protected function setUp(): void
    {
        $this->_protFactoryMock = $this->createMock(BaseFactory::class);
        $this->_dirsMock = $this->createMock(Dir::class);
        $this->_baseConfigMock = $this->createMock(Base::class);
        $this->_moduleListMock = $this->createMock(ModuleListInterface::class);
        $this->directoryReadFactoryMock = $this->createMock(ReadFactory::class);
        $this->_fileIteratorFactory = $this->createMock(FileIteratorFactory::class);

        $this->_model = new Reader(
            $this->_dirsMock,
            $this->_moduleListMock,
            $this->_fileIteratorFactory,
            $this->directoryReadFactoryMock
        );
    }

    public function testGetModuleDirWhenCustomDirIsNotSet()
    {
        $this->_dirsMock->expects(
            $this->any()
        )->method(
            'getDir'
        )->with(
            'Test_Module',
            'etc'
        )->will(
            $this->returnValue('app/code/Test/Module/etc')
        );
        $this->assertEquals(
            'app/code/Test/Module/etc',
            $this->_model->getModuleDir(Dir::MODULE_ETC_DIR, 'Test_Module')
        );
    }

    public function testGetModuleDirWhenCustomDirIsSet()
    {
        $moduleDir = 'app/code/Test/Module/etc/custom';
        $this->_dirsMock->expects($this->never())->method('getDir');
        $this->_model->setModuleDir('Test_Module', 'etc', $moduleDir);
        $this->assertEquals($moduleDir, $this->_model->getModuleDir(Dir::MODULE_ETC_DIR, 'Test_Module'));
    }

    public function testGetConfigurationFiles()
    {
        $configPath = 'app/code/Test/Module/etc/config.xml';
        $modulesDirectoryMock = $this->createMock(ReadInterface::class);
        $modulesDirectoryMock->expects($this->any())->method('getRelativePath')->will($this->returnArgument(0));
        $modulesDirectoryMock->expects($this->any())->method('isExist')
            ->with($configPath)
            ->will($this->returnValue(true));
        $this->directoryReadFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($modulesDirectoryMock));

        $this->_moduleListMock->expects($this->once())->method('getNames')->will($this->returnValue(['Test_Module']));
        $model = new Reader(
            $this->_dirsMock,
            $this->_moduleListMock,
            new FileIteratorFactory(
                new \Magento\Framework\Filesystem\File\ReadFactory(new DriverPool())
            ),
            $this->directoryReadFactoryMock
        );
        $model->setModuleDir('Test_Module', 'etc', 'app/code/Test/Module/etc');

        $this->assertEquals($configPath, $model->getConfigurationFiles('config.xml')->key());
    }

    public function testGetComposerJsonFiles()
    {
        $configPath = 'app/code/Test/Module/composer.json';
        $modulesDirectoryMock = $this->createMock(ReadInterface::class);
        $modulesDirectoryMock->expects($this->any())->method('getRelativePath')->will($this->returnArgument(0));
        $modulesDirectoryMock->expects($this->any())->method('isExist')
            ->with($configPath)
            ->will($this->returnValue(true));
        $this->directoryReadFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($modulesDirectoryMock));

        $this->_moduleListMock->expects($this->once())->method('getNames')->will($this->returnValue(['Test_Module']));
        $model = new Reader(
            $this->_dirsMock,
            $this->_moduleListMock,
            new FileIteratorFactory(
                new \Magento\Framework\Filesystem\File\ReadFactory(new DriverPool())
            ),
            $this->directoryReadFactoryMock
        );
        $model->setModuleDir('Test_Module', '', 'app/code/Test/Module');

        $this->assertEquals($configPath, $model->getComposerJsonFiles()->key());
    }
}
