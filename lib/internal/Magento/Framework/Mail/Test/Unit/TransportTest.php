<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail\Test\Unit;

use Magento\Framework\Mail\Message;
use Magento\Framework\Mail\Transport;
use PHPUnit\Framework\TestCase;

class TransportTest extends TestCase
{
    /**
     * @covers \Magento\Framework\Mail\Transport::sendMessage
     */
    public function testSendMessageBrokenMessage()
    {
        $this->expectException('Magento\Framework\Exception\MailException');
        $this->expectExceptionMessage('Invalid email; contains no at least one of "To", "Cc", and "Bcc" header');
        $transport = new Transport(
            new Message()
        );

        $transport->sendMessage();
    }
}
