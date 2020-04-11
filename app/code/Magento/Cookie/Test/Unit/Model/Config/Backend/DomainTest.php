<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cookie\Test\Unit\Model\Config\Backend;

use Magento\Cookie\Model\Config\Backend\Domain;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Session\Config\Validator\CookieDomainValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test \Magento\Cookie\Model\Config\Backend\Domain
 */
class DomainTest extends TestCase
{
    /** @var AbstractResource|MockObject */
    protected $resourceMock;

    /** @var Domain */
    protected $domain;

    /**
     * @var  CookieDomainValidator|MockObject
     */
    protected $validatorMock;

    protected function setUp(): void
    {
        $eventDispatcherMock = $this->createMock(Manager::class);
        $contextMock = $this->createMock(Context::class);
        $contextMock->expects(
            $this->any()
        )->method(
            'getEventDispatcher'
        )->will(
            $this->returnValue($eventDispatcherMock)
        );

        $this->resourceMock = $this->createPartialMock(AbstractResource::class, [
                '_construct',
                'getConnection',
                'getIdFieldName',
                'beginTransaction',
                'save',
                'commit',
                'addCommitCallback',
                'rollBack',
            ]);

        $this->validatorMock = $this->getMockBuilder(
            CookieDomainValidator::class
        )->disableOriginalConstructor()
            ->getMock();
        $helper = new ObjectManager($this);
        $this->domain = $helper->getObject(
            Domain::class,
            [
                'context' => $contextMock,
                'resource' => $this->resourceMock,
                'configValidator' => $this->validatorMock,
            ]
        );
    }

    /**
     * @covers \Magento\Cookie\Model\Config\Backend\Domain::beforeSave
     * @dataProvider beforeSaveDataProvider
     *
     * @param string $value
     * @param bool $isValid
     * @param int $callNum
     * @param int $callGetMessages
     */
    public function testBeforeSave($value, $isValid, $callNum, $callGetMessages = 0)
    {
        $this->resourceMock->expects($this->any())->method('addCommitCallback')->will($this->returnSelf());
        $this->resourceMock->expects($this->any())->method('commit')->will($this->returnSelf());
        $this->resourceMock->expects($this->any())->method('rollBack')->will($this->returnSelf());

        $this->validatorMock->expects($this->exactly($callNum))
            ->method('isValid')
            ->will($this->returnValue($isValid));
        $this->validatorMock->expects($this->exactly($callGetMessages))
            ->method('getMessages')
            ->will($this->returnValue(['message']));
        $this->domain->setValue($value);
        try {
            $this->domain->beforeSave();
            if ($callGetMessages) {
                $this->fail('Failed to throw exception');
            }
        } catch (LocalizedException $e) {
            $this->assertEquals('Invalid domain name: message', $e->getMessage());
        }
    }

    /**
     * @return array
     */
    public function beforeSaveDataProvider()
    {
        return [
            'not string' => [['array'], false, 1, 1],
            'invalid hostname' => ['http://', false, 1, 1],
            'valid hostname' => ['hostname.com', true, 1, 0],
            'empty string' => ['', false, 0, 0],
        ];
    }
}
