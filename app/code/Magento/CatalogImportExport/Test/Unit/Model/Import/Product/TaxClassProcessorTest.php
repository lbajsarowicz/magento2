<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use PHPUnit\Framework\TestCase;
use Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\ResourceModel\TaxClass\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class TaxClassProcessorTest extends TestCase
{
    const TEST_TAX_CLASS_NAME = 'className';

    const TEST_TAX_CLASS_ID = 1;

    const TEST_JUST_CREATED_TAX_CLASS_ID = 2;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var TaxClassProcessor|MockObject
     */
    protected $taxClassProcessor;

    /**
     * @var AbstractType
     */
    protected $product;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $taxClass = $this->getMockBuilder(ClassModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $taxClass->method('getClassName')->will($this->returnValue(self::TEST_TAX_CLASS_NAME));
        $taxClass->method('getId')->will($this->returnValue(self::TEST_TAX_CLASS_ID));

        $taxClassCollection =
            $this->objectManagerHelper->getCollectionMock(
                Collection::class,
                [$taxClass]
            );

        $taxClassCollectionFactory = $this->createPartialMock(
            \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory::class,
            ['create']
        );

        $taxClassCollectionFactory->method('create')->will($this->returnValue($taxClassCollection));

        $anotherTaxClass = $this->getMockBuilder(ClassModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $anotherTaxClass->method('getClassName')->will($this->returnValue(self::TEST_TAX_CLASS_NAME));
        $anotherTaxClass->method('getId')->will($this->returnValue(self::TEST_JUST_CREATED_TAX_CLASS_ID));

        $taxClassFactory = $this->createPartialMock(\Magento\Tax\Model\ClassModelFactory::class, ['create']);

        $taxClassFactory->method('create')->will($this->returnValue($anotherTaxClass));

        $this->taxClassProcessor =
            new TaxClassProcessor(
                $taxClassCollectionFactory,
                $taxClassFactory
            );

        $this->product =
            $this->getMockForAbstractClass(
                AbstractType::class,
                [],
                '',
                false
            );
    }

    public function testUpsertTaxClassExist()
    {
        $taxClassId = $this->taxClassProcessor->upsertTaxClass(self::TEST_TAX_CLASS_NAME, $this->product);
        $this->assertEquals(self::TEST_TAX_CLASS_ID, $taxClassId);
    }

    public function testUpsertTaxClassNotExist()
    {
        $taxClassId = $this->taxClassProcessor->upsertTaxClass('noExistClassName', $this->product);
        $this->assertEquals(self::TEST_JUST_CREATED_TAX_CLASS_ID, $taxClassId);
    }
}
