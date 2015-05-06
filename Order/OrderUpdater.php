<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\Order;

use Doctrine\Common\Persistence\ObjectManager;
use Sulu\Bundle\Sales\OrderBundle\Order\OrderManger;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OrderUpdater
{
    /**
     * @var Array
     */
    private $scheduledIds;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
        $this->scheduledIds = [];
    }

    /**
     * @return EntityManager
     */
    private function getEntityManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * @return
     * ger
     */
    private function getOrderManager()
    {
        return $this->container->get('sulu_sales_order.order_manager');
    }

    /**
     * Offers depending to an id are going to be updated
     * if processIds() is called.
     *
     * @param string $id
     */
    public function scheduleForUpdate($id)
    {
        if ($id) {
            $this->scheduledIds[] = $id;
        }
    }

    /**
     * Process (update total net price) all offers
     * for the scheduled Items.
     */
    public function processIds()
    {
        $orders = [];
        foreach ($this->scheduledIds as $id) {
            $order = $this->getOrderManager()->findOrderEntityForItemWithId($id);
            if (!in_array($order->getId(), $orders)) {
                $orders[] = $order->getId();
                $order->updateTotalNetPrice();
            }
        }
        unset($this->scheduledIds);
        $this->scheduledIds = [];
        $this->getEntityManager()->flush();
    }
}
