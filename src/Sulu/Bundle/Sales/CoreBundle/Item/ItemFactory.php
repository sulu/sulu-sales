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

use Sulu\Bundle\ProductBundle\Product\ProductFactoryInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\Item;
use Sulu\Bundle\Sales\CoreBundle\Api\Item as ApiItem;
use Sulu\Bundle\PricingBundle\Pricing\PriceFormatter;

class ItemFactory implements ItemFactoryInterface
{
    /**
     * @var string
     */
    protected $defaultCurrencyCode;

    /**
     * @var ProductFactoryInterface
     */
    protected $productFactory;

    /**
     * @var PriceFormatter
     */
    protected $priceFormatter;

    /**
     * @param ProductFactoryInterface $productFactory
     * @param PriceFormatter $priceFormatter
     * @param string $defaultCurrencyCode
     */
    public function __construct(
        $productFactory,
        $priceFormatter,
        $defaultCurrencyCode
    ) {
        $this->productFactory = $productFactory;
        $this->defaultCurrencyCode = $defaultCurrencyCode;
        $this->priceFormatter = $priceFormatter;
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
        $apiItem = new ApiItem($item, $locale, $this->productFactory, $this->priceFormatter, $currency);

        return $apiItem;
    }
}
