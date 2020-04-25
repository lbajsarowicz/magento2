<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Event manager stub
 */
namespace Magento\Framework\Event\Test\Unit;

use Magento\Framework\Event\ManagerInterface;

class ManagerStub implements ManagerInterface
{
    /**
     * Stub dispatch event
     *
     * @param string $eventName
     * @param array $params
     * @return null
     */
    public function dispatch($eventName, array $params = [])
    {
        switch ($eventName) {
            case 'cms_controller_router_match_before':
                $params['condition']->setRedirectUrl('http://www.example.com/');
                break;
        }

        return null;
    }
}
