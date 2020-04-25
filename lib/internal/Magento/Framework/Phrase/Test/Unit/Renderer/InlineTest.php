<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Phrase\Test\Unit\Renderer;

use Magento\Framework\Phrase\Renderer\Inline;
use Magento\Framework\Translate\Inline\ProviderInterface;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\TranslateInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class InlineTest extends TestCase
{
    /**
     * @var TranslateInterface|MockObject
     */
    protected $translator;

    /**
     * @var Inline
     */
    protected $renderer;

    /**
     * @var ProviderInterface|MockObject
     */
    protected $provider;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslateInterface::class);
        $this->provider = $this->createMock(ProviderInterface::class);
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $this->renderer = new Inline(
            $this->translator,
            $this->provider,
            $this->loggerMock
        );
    }

    public function testRenderIfInlineTranslationIsAllowed()
    {
        $theme = 'theme';
        $text = 'test';
        $result = sprintf('{{{%s}}{{%s}}}', $text, $theme);

        $this->translator->expects($this->once())
            ->method('getTheme')
            ->will($this->returnValue($theme));

        $inlineTranslate = $this->createMock(InlineInterface::class);
        $inlineTranslate->expects($this->once())
            ->method('isAllowed')
            ->will($this->returnValue(true));

        $this->provider->expects($this->once())
            ->method('get')
            ->will($this->returnValue($inlineTranslate));

        $this->assertEquals($result, $this->renderer->render([$text], []));
    }

    public function testRenderIfInlineTranslationIsNotAllowed()
    {
        $text = 'test';

        $inlineTranslate = $this->createMock(InlineInterface::class);
        $inlineTranslate->expects($this->once())
            ->method('isAllowed')
            ->will($this->returnValue(false));

        $this->provider->expects($this->once())
            ->method('get')
            ->will($this->returnValue($inlineTranslate));

        $this->assertEquals($text, $this->renderer->render([$text], []));
    }

    public function testRenderException()
    {
        $message = 'something went wrong';
        $exception = new \Exception($message);

        $this->provider->expects($this->once())
            ->method('get')
            ->willThrowException($exception);

        $this->expectException('Exception');
        $this->expectExceptionMessage($message);
        $this->renderer->render(['text'], []);
    }
}
