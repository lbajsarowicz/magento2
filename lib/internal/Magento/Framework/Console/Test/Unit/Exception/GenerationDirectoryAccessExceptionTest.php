<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Console\Test\Unit\Exception;

use Magento\Framework\Console\Exception\GenerationDirectoryAccessException;
use PHPUnit\Framework\TestCase;

class GenerationDirectoryAccessExceptionTest extends TestCase
{
    public function testConstructor()
    {
        $exception = new GenerationDirectoryAccessException();

        $this->assertContains(
            'Command line user does not have read and write permissions on generated directory.',
            $exception->getMessage()
        );
    }
}
