<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Oauth\Test\Unit;

use Magento\Framework\Oauth\OauthInputException;
use Magento\Framework\Phrase;
use PHPUnit\Framework\TestCase;

class OauthInputExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testGetAggregatedErrorMessage()
    {
        $exception = new OauthInputException();
        foreach (['field1', 'field2'] as $param) {
            $exception->addError(
                new Phrase('"%fieldName" is required. Enter and try again.', ['fieldName' => $param])
            );
        }
        $exception->addError(new Phrase('Message with period.'));

        $this->assertEquals(
            '"field1" is required. Enter and try again, "field2" is required. Enter and try again, Message with period',
            $exception->getAggregatedErrorMessage()
        );
    }

    /**
     * @return void
     */
    public function testGetAggregatedErrorMessageNoError()
    {
        $exception = new OauthInputException();
        $this->assertEquals('', $exception->getAggregatedErrorMessage());
    }
}
