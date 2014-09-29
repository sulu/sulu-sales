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
     * @param $orderId
     * @return bool
     */
    public function allowDelete($orderId)
    {
        /** @var SalesDependencyClassInterface $dependency*/
        foreach ($this->dependencyClasses as $dependency) {
            if (!$dependency->allowDelete($orderId)) {
                return false;
            }
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
        /** @var SalesDependencyClassInterface $dependency*/
        foreach ($this->dependencyClasses as $dependency) {
            if (!$dependency->allowCancel($orderId)) {
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
        /** @var SalesDependencyClassInterface $dependency*/
        foreach ($this->dependencyClasses as $dependency) {
            // add to documents array
            $documents = array_merge($documents, $dependency->getDocuments($orderId, $locale));
        }

        return $documents;
    }

}
