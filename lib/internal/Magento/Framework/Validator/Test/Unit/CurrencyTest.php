<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator\Test\Unit;

use Magento\Framework\Setup\Lists;
use Magento\Framework\Validator\Currency;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    /**
     * @var array
     */
    protected $expectedCurrencies = [
        'USD',
        'EUR',
        'UAH',
        'GBP',
    ];

    public function testIsValid()
    {
        $lists = $this->createMock(Lists::class);
        $lists->expects($this->any())->method('getCurrencyList')->will($this->returnValue($this->expectedCurrencies));
        $currency = new Currency($lists);
        $this->assertEquals(true, $currency->isValid('EUR'));
    }
}
