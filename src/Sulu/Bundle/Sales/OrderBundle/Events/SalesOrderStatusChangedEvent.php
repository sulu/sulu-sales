<?php

namespace Sulu\Bundle\Sales\OrderBundle\Events;

use Sulu\Bundle\Sales\OrderBundle\Api\ApiOrderInterface;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderInterface;
use Symfony\Component\EventDispatcher\Event;

class SalesOrderStatusChangedEvent extends Event
{
    /**
     * @var ApiOrderInterface
     */
    protected $order;

    /**
     * @param ApiOrderInterface $order
     */
    public function __construct(ApiOrderInterface $order)
    {
        $this->order = $order;
    }

    /**
     * @return OrderInterface
     */
    public function getOrder()
    {
        return $this->order->getEntity();
    }

    /**
     * @return ApiOrderInterface
     */
    public function getApiOrder()
    {
        return $this->order;
    }
}
