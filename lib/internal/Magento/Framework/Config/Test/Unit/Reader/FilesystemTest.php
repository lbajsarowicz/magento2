<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit\Reader;

use Magento\Framework\Config\ConverterInterface;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Config\ValidationStateInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for
 *
 * @see Filesystem
 */
class FilesystemTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_fileResolverMock;

    /**
     * @var MockObject
     */
    protected $_converterMock;

    /**
     * @var MockObject
     */
    protected $_schemaLocatorMock;

    /**
     * @var MockObject
     */
    protected $_validationStateMock;

    /**
     * @var UrnResolver
     */
    protected $urnResolver;

    /**
     * @var string
     */
    protected $_file;

    protected function setUp(): void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $this->_file = file_get_contents(__DIR__ . '/../_files/reader/config.xml');
        $this->_fileResolverMock = $this->createMock(FileResolverInterface::class);
        $this->_converterMock = $this->createMock(ConverterInterface::class);
        $this->_schemaLocatorMock = $this->createMock(SchemaLocatorInterface::class);
        $this->_validationStateMock = $this->createMock(ValidationStateInterface::class);
        $this->urnResolver = new UrnResolver();
    }

    public function testRead()
    {
        $model = new Filesystem(
            $this->_fileResolverMock,
            $this->_converterMock,
            $this->_schemaLocatorMock,
            $this->_validationStateMock,
            'fileName',
            []
        );
        $this->_fileResolverMock->expects($this->once())->method('get')->will($this->returnValue([$this->_file]));

        $dom = new \DOMDocument();
        $dom->loadXML($this->_file);
        $this->_converterMock->expects($this->once())->method('convert')->with($dom);
        $model->read('scope');
    }

    public function testReadWithoutFiles()
    {
        $model = new Filesystem(
            $this->_fileResolverMock,
            $this->_converterMock,
            $this->_schemaLocatorMock,
            $this->_validationStateMock,
            'fileName',
            []
        );
        $this->_fileResolverMock
            ->expects($this->once())->method('get')->will($this->returnValue([]));

        $this->assertEmpty($model->read('scope'));
    }

    public function testReadWithInvalidDom()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Invalid Document');
        $this->_schemaLocatorMock->expects(
            $this->once()
        )->method(
            'getSchema'
        )->will(
            $this->returnValue(
                $this->urnResolver->getRealPath('urn:magento:framework:Config/Test/Unit/_files/reader/schema.xsd')
            )
        );
        $this->_validationStateMock->expects($this->any())
            ->method('isValidationRequired')
            ->willReturn(true);
        $model = new Filesystem(
            $this->_fileResolverMock,
            $this->_converterMock,
            $this->_schemaLocatorMock,
            $this->_validationStateMock,
            'fileName',
            []
        );
        $this->_fileResolverMock->expects($this->once())->method('get')->will($this->returnValue([$this->_file]));

        $model->read('scope');
    }

    public function testReadWithInvalidXml()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('The XML in file "0" is invalid:');
        $this->_schemaLocatorMock->expects(
            $this->any()
        )->method(
            'getPerFileSchema'
        )->will(
            $this->returnValue(
                $this->urnResolver->getRealPath('urn:magento:framework:Config/Test/Unit/_files/reader/schema.xsd')
            )
        );
        $this->_validationStateMock->expects($this->any())
            ->method('isValidationRequired')
            ->willReturn(true);

        $model = new Filesystem(
            $this->_fileResolverMock,
            $this->_converterMock,
            $this->_schemaLocatorMock,
            $this->_validationStateMock,
            'fileName',
            []
        );
        $this->_fileResolverMock->expects($this->once())->method('get')->will($this->returnValue([$this->_file]));
        $model->read('scope');
    }

    public function testReadException()
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Instance of the DOM config merger is expected, got StdClass instead.');
        $this->_fileResolverMock->expects($this->once())->method('get')->will($this->returnValue([$this->_file]));
        $model = new Filesystem(
            $this->_fileResolverMock,
            $this->_converterMock,
            $this->_schemaLocatorMock,
            $this->_validationStateMock,
            'fileName',
            [],
            'StdClass'
        );
        $model->read();
    }
}
