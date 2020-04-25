<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Route\Config;

use Magento\Framework\App\Route\Config\Converter;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new Converter();
    }

    public function testConvert()
    {
        $basePath = realpath(__DIR__) . '/_files/';
        $path = $basePath . 'routes.xml';
        $domDocument = new \DOMDocument();
        $domDocument->load($path);
        $expectedData = include $basePath . 'routes.php';
        $this->assertEquals($expectedData, $this->_model->convert($domDocument));
    }
}
