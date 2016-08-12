<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\Tests\Functional\Manager;

use JMS\Serializer\EventDispatcher\EventDispatcherInterface;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus;
use Sulu\Bundle\Sales\OrderBundle\Events\SalesOrderStatusChangedEvent;
use Sulu\Bundle\Sales\OrderBundle\Order\OrderFactory;
use Sulu\Bundle\Sales\OrderBundle\Order\OrderManager;
use Sulu\Bundle\Sales\OrderBundle\Tests\OrderTestBase;

class OrderManagerTest extends OrderTestBase
{
    /**
     * Check if SalesOrderStatusChanged event is triggered when changing status of an order.
     */
    public function testOrderStatusChangedEvent()
    {
        // Create eventlistener in order to check if event has been dispatched.
        $isEventDispatched = false;
        $this->getEventDispatcher()->addListener(
            $this->getContainer()->getParameter('sulu_sales_order.events.order_status_changed'),
            function (SalesOrderStatusChangedEvent $event) use (&$isEventDispatched) {
                $this->assertNotNull($event->getOrder());
                $this->assertNotNull($event->getApiOrder());
                $isEventDispatched = true;
            }
        );

        $apiOrder = $this->getOrderFactory()->createApiEntity($this->data->order, 'en');

        // Change status.
        $this->getOrderManager()->convertStatus($apiOrder, OrderStatus::STATUS_COMPLETED);

        // Check if event has been dispatched.
        $this->assertTrue($isEventDispatched);
    }

    /**
     * @return OrderFactory
     */
    protected function getOrderFactory()
    {
        return $this->getContainer()->get('sulu_sales_order.order_factory');
    }

    /**
     * @return OrderManager
     */
    protected function getOrderManager()
    {
        return $this->getContainer()->get('sulu_sales_order.order_manager');
    }

    /**
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        return $this->getContainer()->get('event_dispatcher');
    }
}
