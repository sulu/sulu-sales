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

use Sulu\Bundle\Sales\CoreBundle\Entity\ItemInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\Item;
use Sulu\Bundle\Sales\CoreBundle\Api\Item as ApiItem;

class ItemFactory implements ItemFactoryInterface
{
    private $defaultCurrencyCode;

    /**
     * @param string $apiProductEntity
     */
    public function __construct(
        $apiProductEntity,
        $defaultCurrencyCode
    ) {
        // FIXME: guess this is not the way this should be done
        ApiItem::$productEntity = $apiProductEntity;

        $this->defaultCurrencyCode = $defaultCurrencyCode;
    }

    /**
     * {@inheritdoc}
     */
    public function createEntity()
    {
        return new Item();
    }

    /**
     * {@inheritdoc}
     */
    public function createApiEntity(ItemInterface $item, $locale, $currency = null)
    {
        if (!$currency) {
            $currency = $this->defaultCurrencyCode;
        }
        $apiItem = new ApiItem($item, $locale, $currency);

        return $apiItem;
    }
}
