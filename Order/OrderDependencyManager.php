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

use Sulu\Bundle\Sales\CoreBundle\SalesDependency\AbstractSalesDependency;
use Sulu\Bundle\Sales\CoreBundle\SalesDependency\SalesDependencyClassInterface;
use Sulu\Bundle\Sales\OrderBundle\Api\Order;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus;

/**
 * Class OrderPersmission
 * @package Sulu\Bundle\Sales\OrderBundle\Order
 */
class OrderDependencyManager extends AbstractSalesDependency implements SalesDependencyClassInterface
{
    /**
     * returns name of the dependency class
     * @return string
     */
    public function getName()
    {
        return 'order';
    }

    /**
     * returns array of parameters
     * @param Order $order
     * @return bool
     */
    public function allowDelete($order)
    {
        // TODO: check if order once was confirmed (lookup in activity log)

        // check if order is confirmed
        if ($order->getStatus()->getId() >= OrderStatus::STATUS_CONFIRMED) {
            return false;
        }
        /** @var SalesDependencyClassInterface $dependency */
        foreach ($this->dependencyClasses as $dependency) {
            if (!$dependency->allowDelete($order)) {
                return false;
            }
        }
        return true;
    }

    /**
     * returns the identifying name
     * @param Order $order
     * @return bool
     */
    public function allowCancel($order)
    {
        /** @var SalesDependencyClassInterface $dependency */
        foreach ($this->dependencyClasses as $dependency) {
            if (!$dependency->allowCancel($order)) {
                return false;
            }
        }
        return true;
    }

    /**
     *
     * @param $orderId
     * @param $locale
     * @return array
     */
    public function getDocuments($orderId, $locale)
    {
        $documents = array();
        /** @var SalesDependencyClassInterface $dependency */
        foreach ($this->dependencyClasses as $dependency) {
            // add to documents array
            $documents = array_merge($documents, $dependency->getDocuments($orderId, $locale));
        }

        return $documents;
    }

    /**
     * returns all possible workflows for the current entity
     *
     * @param Order $order
     * @return array
     */
    public function getWorkflows($order)
    {
        $workflows = array();
        $actions = array(
            'confirm' => array(
                'section' => $this->getName(),
                'title' => 'salesorder.orders.confirm',
                'event' => 'sulu.salesorder.order.confirm.clicked'
            ),
            'edit' => array(
                'section' => $this->getName(),
                'title' => 'salesorder.orders.edit',
                'event' => 'sulu.salesorder.order.edit.clicked'
            ),
            'delete' => array(
                'section' => $this->getName(),
                'title' => 'salesorder.orders.delete',
                'event' => 'sulu.salesorder.order.delete',
                'parameters'=> array('id'=> $order->getId())
            ),
        );

        // define workflows by order's status
        $orderStatusId = $order->getStatus()->getId();
        // order is in created state
        if ($orderStatusId === OrderStatus::STATUS_CREATED) {
            $workflows[] = $actions['confirm'];
        }
        // order is confirmed
        else if ($orderStatusId === OrderStatus::STATUS_CONFIRMED) {
            $workflows[] = $actions['edit'];
        }

        // order is allowed to be deleted
        if ($this->allowDelete($order)) {
            $workflows[] = $actions['delete'];
        }

        // get workflows from dependencies
        /** @var SalesDependencyClassInterface $dependency */
        foreach ($this->dependencyClasses as $dependency) {
            $workflows = array_merge($workflows, $dependency->getWorkflows($order));
        }
        return $workflows;
    }
}
