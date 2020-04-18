<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import;

use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit\Framework\TestCase;
use Magento\MediaStorage\Helper\File\Storage\Database;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\MediaStorage\Helper\File\Storage;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\Math\Random;
use Magento\CatalogImportExport\Model\Import\Uploader;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\File\Read;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\Driver\Http;
use Magento\Framework\Filesystem\Driver\Https;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UploaderTest extends TestCase
{
    /**
     * @var Database|MockObject
     */
    protected $coreFileStorageDb;

    /**
     * @var Storage|MockObject
     */
    protected $coreFileStorage;

    /**
     * @var AdapterFactory|MockObject
     */
    protected $imageFactory;

    /**
     * @var NotProtectedExtension|MockObject
     */
    protected $validator;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystem;

    /**
     * @var ReadFactory|MockObject
     */
    protected $readFactory;

    /**
     * @var WriteInterface|MockObject
     */
    protected $directoryMock;

    /**
     * @var Random|MockObject
     */
    private $random;

    /**
     * @var Uploader|MockObject
     */
    protected $uploader;

    protected function setUp(): void
    {
        $this->coreFileStorageDb = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->coreFileStorage = $this->getMockBuilder(Storage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageFactory = $this->getMockBuilder(AdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator = $this->getMockBuilder(
            NotProtectedExtension::class
        )->disableOriginalConstructor()->getMock();

        $this->readFactory = $this->getMockBuilder(ReadFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->directoryMock = $this->getMockBuilder(WriteInterface::class)
            ->setMethods(['writeFile', 'getRelativePath', 'isWritable', 'getAbsolutePath'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDirectoryWrite'])
            ->getMock();
        $this->filesystem->expects($this->any())
                        ->method('getDirectoryWrite')
                        ->will($this->returnValue($this->directoryMock));

        $this->random = $this->getMockBuilder(Random::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRandomString'])
            ->getMock();

        $this->uploader = $this->getMockBuilder(Uploader::class)
            ->setConstructorArgs(
                [
                    $this->coreFileStorageDb,
                    $this->coreFileStorage,
                    $this->imageFactory,
                    $this->validator,
                    $this->filesystem,
                    $this->readFactory,
                    null,
                    $this->random
                ]
            )
            ->setMethods(['_setUploadFile', 'save', 'getTmpDir', 'checkAllowedExtension'])
            ->getMock();
    }

    /**
     * @dataProvider moveFileUrlDataProvider
     * @param $fileUrl
     * @param $expectedHost
     * @param $expectedFileName
     * @param $checkAllowedExtension
     * @throws LocalizedException
     */
    public function testMoveFileUrl($fileUrl, $expectedHost, $expectedFileName, $checkAllowedExtension)
    {
        $tmpDir = 'var/tmp';
        $destDir = 'var/dest/dir';
        $this->uploader->method('getTmpDir')->willReturn($tmpDir);

        // Expected invocation to validate file extension
        $this->uploader->expects($this->exactly($checkAllowedExtension))->method('checkAllowedExtension')
            ->willReturn(true);

        // Expected invocation to generate random string for file name postfix
        $this->random->expects($this->once())->method('getRandomString')
            ->with(16)
            ->willReturn('38GcEmPFKXXR8NMj');

        // Expected invocation to build the temp file path with the correct directory and filename
        $this->directoryMock->expects($this->any())->method('getRelativePath')
            ->with($tmpDir . '/' . $expectedFileName);

        // Create adjusted reader which does not validate path.
        $readMock = $this->getMockBuilder(Read::class)
            ->disableOriginalConstructor()
            ->setMethods(['readAll'])
            ->getMock();

        // Expected invocations to create reader and read contents from url
        $this->readFactory->expects($this->once())->method('create')
            ->with($expectedHost)
            ->will($this->returnValue($readMock));
        $readMock->expects($this->once())->method('readAll')
            ->will($this->returnValue(null));

        // Expected invocation to write the temp file
        $this->directoryMock->expects($this->any())->method('writeFile')
            ->will($this->returnValue($expectedFileName));

        // Expected invocations save the downloaded file to temp file
        // and move the temp file to the destination directory
        $this->directoryMock->expects($this->exactly(2))
            ->method('isWritable')
            ->withConsecutive([$destDir], [$tmpDir])
            ->willReturn(true);
        $this->directoryMock->expects($this->once())->method('getAbsolutePath')
            ->with($destDir)
            ->willReturn($destDir . '/' . $expectedFileName);
        $this->uploader->expects($this->once())->method('_setUploadFile')
            ->willReturnSelf();
        $this->uploader->expects($this->once())->method('save')
            ->with($destDir . '/' . $expectedFileName)
            ->willReturn(['name' => $expectedFileName, 'path' => 'absPath']);

        $this->uploader->setDestDir($destDir);
        $result = $this->uploader->move($fileUrl);

        $this->assertEquals(['name' => $expectedFileName], $result);
        $this->assertArrayNotHasKey('path', $result);
    }

    public function testMoveFileName()
    {
        $destDir = 'var/dest/dir';
        $fileName = 'test_uploader_file';
        $expectedRelativeFilePath = $fileName;
        $this->directoryMock->expects($this->once())->method('isWritable')->with($destDir)->willReturn(true);
        $this->directoryMock->expects($this->any())->method('getRelativePath')->with($expectedRelativeFilePath);
        $this->directoryMock->expects($this->once())->method('getAbsolutePath')->with($destDir)
            ->willReturn($destDir . '/' . $fileName);
        //Check invoking of getTmpDir(), _setUploadFile(), save() methods.
        $this->uploader->expects($this->once())->method('getTmpDir')->will($this->returnValue(''));
        $this->uploader->expects($this->once())->method('_setUploadFile')->will($this->returnSelf());
        $this->uploader->expects($this->once())->method('save')->with($destDir . '/' . $fileName)
            ->willReturn(['name' => $fileName]);

        $this->uploader->setDestDir($destDir);
        $this->assertEquals(['name' => $fileName], $this->uploader->move($fileName));
    }

    /**
     * @dataProvider moveFileUrlDriverPoolDataProvider
     */
    public function testMoveFileUrlDrivePool($fileUrl, $expectedHost, $expectedDriverPool, $expectedScheme)
    {
        $driverPool = $this->createPartialMock(DriverPool::class, ['getDriver']);
        $driverMock = $this->createPartialMock($expectedDriverPool, ['readAll', 'isExists']);
        $driverMock->expects($this->any())->method('isExists')->willReturn(true);
        $driverMock->expects($this->any())->method('readAll')->willReturn(null);
        $driverPool->expects($this->any())->method('getDriver')->willReturn($driverMock);

        $readFactory = $this->getMockBuilder(ReadFactory::class)
            ->setConstructorArgs(
                [
                    $driverPool,
                ]
            )
            ->setMethods(['create'])
            ->getMock();

        $readFactory->expects($this->any())->method('create')
            ->with($expectedHost, $expectedScheme)
            ->willReturn($driverMock);

        $uploaderMock = $this->getMockBuilder(Uploader::class)
            ->setConstructorArgs(
                [
                    $this->coreFileStorageDb,
                    $this->coreFileStorage,
                    $this->imageFactory,
                    $this->validator,
                    $this->filesystem,
                    $readFactory,
                ]
            )
            ->getMock();

        $result = $uploaderMock->move($fileUrl);
        $this->assertNull($result);
    }

    /**
     * @return array
     */
    public function moveFileUrlDriverPoolDataProvider()
    {
        return [
            [
                '$fileUrl'              => 'http://test_uploader_file',
                '$expectedHost'         => 'test_uploader_file',
                '$expectedDriverPool'   => Http::class,
                '$expectedScheme'       => DriverPool::HTTP,
            ],
            [
                '$fileUrl'              => 'https://!:^&`;file',
                '$expectedHost'         => '!:^&`;file',
                '$expectedDriverPool'   => Https::class,
                '$expectedScheme'       => DriverPool::HTTPS,
            ],
        ];
    }

    /**
     * @return array
     */
    public function moveFileUrlDataProvider()
    {
        return [
            'https_no_file_ext' => [
                '$fileUrl' => 'https://test_uploader_file',
                '$expectedHost' => 'test_uploader_file',
                '$expectedFileName' => 'test_uploader_file_38GcEmPFKXXR8NMj',
                '$checkAllowedExtension' => 0
            ],
            'https_invalid_chars' => [
                '$fileUrl' => 'https://www.google.com/!:^&`;image.jpg',
                '$expectedHost' => 'www.google.com/!:^&`;image.jpg',
                '$expectedFileName' => 'image_38GcEmPFKXXR8NMj.jpg',
                '$checkAllowedExtension' => 1
            ],
            'https_invalid_chars_no_file_ext' => [
                '$fileUrl' => 'https://!:^&`;image',
                '$expectedHost' => '!:^&`;image',
                '$expectedFileName' => 'image_38GcEmPFKXXR8NMj',
                '$checkAllowedExtension' => 0
            ],
            'http_jpg' => [
                '$fileUrl' => 'http://www.google.com/image.jpg',
                '$expectedHost' => 'www.google.com/image.jpg',
                '$expectedFileName' => 'image_38GcEmPFKXXR8NMj.jpg',
                '$checkAllowedExtension' => 1
            ],
            'https_jpg' => [
                '$fileUrl' => 'https://www.google.com/image.jpg',
                '$expectedHost' => 'www.google.com/image.jpg',
                '$expectedFileName' => 'image_38GcEmPFKXXR8NMj.jpg',
                '$checkAllowedExtension' => 1
            ],
            'https_jpeg' => [
                '$fileUrl' => 'https://www.google.com/image.jpeg',
                '$expectedHost' => 'www.google.com/image.jpeg',
                '$expectedFileName' => 'image_38GcEmPFKXXR8NMj.jpeg',
                '$checkAllowedExtension' => 1
            ],
            'https_png' => [
                '$fileUrl' => 'https://www.google.com/image.png',
                '$expectedHost' => 'www.google.com/image.png',
                '$expectedFileName' => 'image_38GcEmPFKXXR8NMj.png',
                '$checkAllowedExtension' => 1
            ],
            'https_gif' => [
                '$fileUrl' => 'https://www.google.com/image.gif',
                '$expectedHost' => 'www.google.com/image.gif',
                '$expectedFileName' => 'image_38GcEmPFKXXR8NMj.gif',
                '$checkAllowedExtension' => 1
            ],
            'https_one_query_param' => [
                '$fileUrl' => 'https://www.google.com/image.jpg?param=1',
                '$expectedHost' => 'www.google.com/image.jpg?param=1',
                '$expectedFileName' => 'image_38GcEmPFKXXR8NMj.jpg',
                '$checkAllowedExtension' => 1
            ],
            'https_two_query_params' => [
                '$fileUrl' => 'https://www.google.com/image.jpg?param=1&param=2',
                '$expectedHost' => 'www.google.com/image.jpg?param=1&param=2',
                '$expectedFileName' => 'image_38GcEmPFKXXR8NMj.jpg',
                '$checkAllowedExtension' => 1
            ]
        ];
    }
}
