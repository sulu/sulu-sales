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

use Proxies\__CG__\Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus;
use Sulu\Bundle\Sales\CoreBundle\SalesDependency\AbstractSalesDependency;
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

    /**
     * @var ShippingManager
     */
    private $shippingManager;

    /**
     * constructor
     */
    public function __construct(ShippingManager $shippingManager)
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
     * @param $orderId
     * @return bool
     */
    public function allowDelete($orderId)
    {
        // do not allow order to be deleted, if a shipping exists for the given
        // order
        if ($this->shippingManager->countByOrderId($orderId) > 0) {
            return false;
        }
        return true;
    }

    /**
     * returns the identifying name
     * @param $orderId
     * @return bool
     */
    public function allowCancel($orderId)
    {
        // do not allow order to be canceled, if a shipping exists for the given
        // order that already is shipped
        if ($this->shippingManager->countByOrderId($orderId, array(ShippingStatus::STATUS_SHIPPED)) > 0) {
            return false;
        }
        return true;
    }

    /**
     *
     * @param $orderId
     * @return array
     */
    public function getDocuments($orderId)
    {
        $documents = array();
        return $documents;
    }

}
