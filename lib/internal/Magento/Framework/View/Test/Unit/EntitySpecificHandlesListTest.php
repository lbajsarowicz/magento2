<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\EntitySpecificHandlesList;

class EntitySpecificHandlesListTest extends TestCase
{
    /**
     * @var EntitySpecificHandlesList
     */
    private $entitySpecificHandlesList;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->entitySpecificHandlesList = $objectManager->getObject(EntitySpecificHandlesList::class);
    }

    public function testAddAndGetHandles()
    {
        $this->assertEquals([], $this->entitySpecificHandlesList->getHandles());
        $this->entitySpecificHandlesList->addHandle('handle1');
        $this->entitySpecificHandlesList->addHandle('handle2');
        $this->assertEquals(['handle1', 'handle2'], $this->entitySpecificHandlesList->getHandles());
    }
}
