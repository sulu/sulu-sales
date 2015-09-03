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

use Sulu\Bundle\Sales\OrderBundle\Api\ApiOrderInterface;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderInterface;

interface OrderFactoryInterface
{
    /**
     * Creates a new entity
     *
     * @return OrderInterface
     */
    public function createEntity();

    /**
     * Creates a new api entity
     *
     * @param OrderInterface $order
     * @param string $locale
     *
     * @return ApiOrderInterface
     */
    public function createApiEntity(OrderInterface $order, $locale);
}
