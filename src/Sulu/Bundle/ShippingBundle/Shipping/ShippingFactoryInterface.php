<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\ShippingBundle\Shipping;

use Sulu\Bundle\Sales\ShippingBundle\Api\Shipping as ApiShipping;
use Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping;

interface ShippingFactoryInterface
{
    /**
     * Creates a new entity
     *
     * @return Shipping
     */
    public function createEntity();

    /**
     * Creates a new api entity
     *
     * @param Shipping $shipping
     * @param string $locale
     *
     * @return ApiShipping
     */
    public function createApiEntity(Shipping $shipping, $locale);
}
