<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\Entity\Order;

use Sulu\Bundle\Sales\CoreBundle\Item\ItemFactoryInterface;
use Sulu\Bundle\Sales\OrderBundle\Api\Order as ApiOrder;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderInterface;

class OrderFactory implements OrderFactoryInterface
{
    private $itemFactory;

    /**
     * @param ItemFactoryInterface $itemFactory
     */
    public function __construct(
        ItemFactoryInterface $itemFactory
    ) {
        $this->itemFactory = $itemFactory;
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
    public function createApiEntity(OrderInterface $item, $locale)
    {
        return new ApiOrder($item, $locale, $this->itemFactory);
    }
}
