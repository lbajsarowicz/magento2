<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Test\Unit\DomDocument;

use Magento\Framework\DomDocument\DomDocumentFactory;
use PHPUnit\Framework\TestCase;

class DomDocumentFactoryTest extends TestCase
{
    public function testCreateReturnsDomDocument()
    {
        $domDocumentFactory = new DomDocumentFactory();
        $this->assertInstanceOf(
            \DOMDocument::class,
            $domDocumentFactory->create()
        );
    }
}
