<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Indexer\Stock\Plugin;

use PHPUnit\Framework\TestCase;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\ImportExport\Model\Import;

class ImportTest extends TestCase
{
    public function testAfterImportSource()
    {
        /**
         * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor|
         *      \PHPUnit_Framework_MockObject_MockObject $processorMock
         */
        $processorMock = $this->createPartialMock(
            Processor::class,
            ['markIndexerAsInvalid', 'isIndexerScheduled']
        );

        $subjectMock = $this->createMock(Import::class);
        $processorMock->expects($this->any())->method('markIndexerAsInvalid');
        $processorMock->expects($this->any())->method('isIndexerScheduled')->willReturn(false);

        $someData = [1, 2, 3];

        $model = new \Magento\CatalogImportExport\Model\Indexer\Stock\Plugin\Import($processorMock);
        $this->assertEquals($someData, $model->afterImportSource($subjectMock, $someData));
    }
}
