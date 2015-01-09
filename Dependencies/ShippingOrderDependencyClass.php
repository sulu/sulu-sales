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
use Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus;
use Sulu\Bundle\Sales\CoreBundle\SalesDependency\SalesDependencyClassInterface;
use Sulu\Bundle\Sales\ShippingBundle\Shipping\ShippingManager;

/**
 * Class OrderPersmission
 *
 * @package Sulu\Bundle\Sales\OrderBundle\Order
 */
class ShippingOrderDependencyClass implements SalesDependencyClassInterface
{
    /**
     * this dependendencies name
     *
     * @var string
     */
    private $name = 'shipping';

    protected $routes;

    /**
     * @var ShippingManager
     */
    private $shippingManager;

    /**
     * constructor
     */
    public function __construct(ShippingManager $shippingManager, array $routes)
    {
        $this->shippingManager = $shippingManager;
        $this->routes = $routes;
    }

    /**
     * returns name of the dependency class
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * returns if the order with the given order ID can be deleted
     *
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
     *
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
     * defines if shipping can be added
     *
     * @param $order
     * @return bool
     */
    public function allowShippingAdd($order) {
        return ($order->getStatus()->getId() === OrderStatus::STATUS_CONFIRMED);
    }

    /**
     * Returns shippings which are associated with an order
     *
     * @param $orderId
     * @param $locale
     * @return array
     */
    public function getDocuments($orderId, $locale)
    {
        return $this->shippingManager->findByOrderId($orderId, $locale);
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
                    'route' => $this->getRoute($order->getId(), 'order', 'shippings'),
                ),
            );
        }

        return $workflows;
    }

    /**
     * Returns uri for shippings
     *
     * @param $id
     * @param string $subject
     * @param string $type
     * @return string
     */
    protected function getRoute($id, $subject, $type)
    {
        if (!is_null($this->routes) &&
            array_key_exists($subject, $this->routes) &&
            array_key_exists($type, $this->routes[$subject])
        ) {
            return str_replace('[id]', $id, $this->routes[$subject][$type]);
        }

        return '';
    }
}
