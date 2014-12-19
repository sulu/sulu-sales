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

class OrderUpdater
{
    /**
     * @var Array
     */
    private $scheduledIds;

    /**
     * @var OrderManager
     */
    private $orderManager;

    /**
     * @var ObjectManager
     */
    private $em;

    public function __construct(
        ObjectManager $em,
        OrderManager $orderManager
    ) {
        $this->em = $em;
        $this->orderManager = $orderManager;
        $this->scheduledIds = [];
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
            $order = $this->orderManager->findOrderEntityForItemWithId($id);
            if (!in_array($order->getId(), $orders)) {
                $orders[] = $order->getId();
                $order->updateTotalNetPrice();
            }
        }
        unset($this->scheduledIds);
        $this->scheduledIds = [];
        $this->em->flush();
    }
}
