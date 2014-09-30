<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\ShippingBundle\Dependencies;

use Sulu\Bundle\Sales\OrderBundle\Api\Order;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus;
use Sulu\Bundle\Sales\OrderBundle\Order\OrderManager;
use Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping;
use Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus;
use Sulu\Bundle\Sales\CoreBundle\SalesDependency\SalesDependencyClassInterface;
use Sulu\Bundle\Sales\ShippingBundle\Shipping\ShippingManager;

/**
 * Class OrderPersmission
 * @package Sulu\Bundle\Sales\OrderBundle\Order
 */
class ShippingOrderDependencyClass implements SalesDependencyClassInterface
{
    /**
     * this dependendencies name
     * @var string
     */
    private $name = 'shipping';

    private $orderBaseUrl = 'sales/orders';

    /**
     * @var ShippingManager
     */
    private $shippingManager;

    /**
     * constructor
     */
    public function __construct(
        ShippingManager $shippingManager
    )
    {
        $this->shippingManager = $shippingManager;
    }

    /**
     * returns name of the dependency class
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * returns if the order with the given order ID can be deleted
     * @param $order
     * @return bool
     */
    public function allowDelete($order)
    {
        // do not allow order to be deleted, if a shipping exists for the given
        // order
        if ($this->shippingManager->countByOrderId($order->getId()) > 0) {
            return false;
        }
        return true;
    }

    /**
     * returns the identifying name
     * @param $order
     * @return bool
     */
    public function allowCancel($order)
    {
        // do not allow order to be canceled, if a shipping exists for the given
        // order that already is shipped
        if ($this->shippingManager->countByOrderId($order->getId(), array(ShippingStatus::STATUS_SHIPPED)) > 0) {
            return false;
        }
        return true;
    }

    /**
     *
     * @param $order
     * @return array
     */
    public function getDocuments($order)
    {
        // TODO: still needs to be implemented
        $documents = array();
        return $documents;
    }

    /**
     * @param Order $order
     * @return array
     */
    public function getWorkflows($order)
    {
        $workflows = array();

        // allow to add orders as long as Order status is confirmed
        if ($order->getStatus()->getId() === OrderStatus::STATUS_CONFIRMED) {
            $workflows = array(
                array(
                    'section' => $this->getName(),
                    'title' => 'salesorder.orders.shipping.create',
                    'route' => $this->getOrderUrl($order, 'shippings/add')
                ),
            );
        }

        return $workflows;
    }

    /**
     * @param $order
     * @param $postFix
     * @return string
     */
    private function getOrderUrl($order, $postFix)
    {
        return $this->orderBaseUrl . '/edit:'.$order->getId().'/'.$postFix;
    }
}
