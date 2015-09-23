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

class OrderDependencyManager extends AbstractSalesDependency implements SalesDependencyClassInterface
{
    /**
     * Returns name of the dependency class.
     *
     * @return string
     */
    public function getName()
    {
        return 'order';
    }

    /**
     * Returns array of parameters.
     *
     * @param Order $order
     *
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
     * Returns the identifying name.
     *
     * @param Order $order
     *
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
     * Returns all Documents.
     *
     * @param int $orderId
     * @param string $locale
     *
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
     * Returns all possible workflows for the current entity.
     *
     * @param Order $order
     *
     * @return array
     */
    public function getWorkflows($order)
    {
        $workflows = array(
            'confirm' => array(
                'section' => $this->getName(),
                'title' => 'salesorder.orders.confirm',
                'event' => 'sulu.salesorder.order.confirm.clicked',
                'disabled' => true,
            ),
            'edit' => array(
                'section' => $this->getName(),
                'title' => 'salesorder.orders.edit',
                'event' => 'sulu.salesorder.order.edit.clicked',
                'disabled' => true,
            ),
        );

        // define workflows by order's status
        $orderStatusId = $order->getStatus()->getId();
        // order is in created state
        if ($orderStatusId === OrderStatus::STATUS_CREATED) {
            $workflows['confirm']['disabled'] = false;
        } // order is confirmed
        else if ($orderStatusId === OrderStatus::STATUS_CONFIRMED) {
            $workflows['edit']['disabled'] = false;
        }

        // get workflows from dependencies
        /** @var SalesDependencyClassInterface $dependency */
        foreach ($this->dependencyClasses as $dependency) {
            $workflows = array_merge($workflows, $dependency->getWorkflows($order));
        }

        return array_values($workflows);
    }
}
