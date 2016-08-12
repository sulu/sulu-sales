<?php

namespace Sulu\Bundle\Sales\OrderBundle\Events;

class SalesOrderEvents
{
    /**
     * Event is triggered when the status of an order has changed.
     */
    const STATUS_CHANGED = 'sulu_sales_order.status_changed';
}
