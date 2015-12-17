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

use Sulu\Bundle\Sales\CoreBundle\Item\ItemFactoryInterface;
use Sulu\Bundle\Sales\CoreBundle\Pricing\PriceFormatter;
use Sulu\Bundle\Sales\ShippingBundle\Api\Shipping as ApiShipping;
use Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping;

class ShippingFactory implements ShippingFactoryInterface
{
    private $itemFactory;

    /**
     * @var PriceFormatter
     */
    private $priceFormatter;

    /**
     * @param ItemFactoryInterface $itemFactory
     * @param PriceFormatter $priceFormatter
     */
    public function __construct(
        ItemFactoryInterface $itemFactory,
        PriceFormatter $priceFormatter
    ) {
        $this->itemFactory = $itemFactory;
        $this->priceFormatter = $priceFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function createEntity()
    {
        return new Shipping();
    }

    /**
     * {@inheritdoc}
     */
    public function createApiEntity(Shipping $shipping, $locale)
    {
        return new ApiShipping($shipping, $locale, $this->itemFactory, $this->priceFormatter);
    }
}
