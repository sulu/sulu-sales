<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\Item;

use Sulu\Bundle\Sales\CoreBundle\Api\ApiItemInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemInterface;

interface ItemFactoryInterface
{
    /**
     * Creates a new entity
     *
     * @return ItemInterface
     */
    public function createEntity();

    /**
     * Creates a new api entity
     *
     * @param ItemInterface $item
     * @param string $locale
     * @param string $currency
     *
     * @return ApiItemInterface
     */
    public function createApiEntity(ItemInterface $item, $locale, $currency = null);
}
