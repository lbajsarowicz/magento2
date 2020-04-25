<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator\Test\Unit;

use Magento\Framework\Setup\Lists;
use Magento\Framework\Validator\Timezone;
use PHPUnit\Framework\TestCase;

class TimezoneTest extends TestCase
{
    /**
     * @var array
     */
    protected $expectedTimezones = [
        'Australia/Darwin',
        'America/Los_Angeles',
        'Europe/Kiev',
        'Asia/Jerusalem',
    ];

    public function testIsValid()
    {
        $lists = $this->createMock(Lists::class);
        $lists->expects($this->any())->method('getTimezoneList')->will($this->returnValue($this->expectedTimezones));
        $timezone = new Timezone($lists);
        $this->assertEquals(true, $timezone->isValid('America/Los_Angeles'));
    }
}
