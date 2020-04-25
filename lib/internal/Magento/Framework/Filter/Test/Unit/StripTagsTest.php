<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\Test\Unit;

use Magento\Framework\Escaper;
use Magento\Framework\Filter\StripTags;
use PHPUnit\Framework\TestCase;

class StripTagsTest extends TestCase
{
    /**
     * @covers \Magento\Framework\Filter\StripTags::filter
     */
    public function testStripTags()
    {
        $stripTags = new StripTags(new Escaper());
        $this->assertEquals('three', $stripTags->filter('<two>three</two>'));
    }
}
