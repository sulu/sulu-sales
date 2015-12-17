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

use Sulu\Bundle\Sales\CoreBundle\Item\ItemFactoryInterface;
use Sulu\Bundle\PricingBundle\Pricing\PriceFormatter;
use Sulu\Bundle\Sales\OrderBundle\Api\Order as ApiOrder;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderInterface;
use Sulu\Bundle\Sales\OrderBundle\Entity\Order;

class OrderFactory implements OrderFactoryInterface
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
        return new Order();
    }

    /**
     * {@inheritdoc}
     */
    public function createApiEntity(OrderInterface $order, $locale)
    {
        return new ApiOrder($order, $locale, $this->itemFactory, $this->priceFormatter);
    }
}
