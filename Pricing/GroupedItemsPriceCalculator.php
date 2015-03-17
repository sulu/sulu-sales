<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\Pricing;

use Sulu\Bundle\ProductBundle\Product\ProductPriceManagerInterface;
use Sulu\Bundle\Sales\CoreBundle\Pricing\Exceptions\PriceCalculationException;

/**
 * Calculate Price of an Order
 */
class GroupedItemsPriceCalculator implements GroupedItemsPriceCalculatorInterface
{
    protected $itemPriceCalculator;

    public function __construct(ItemPriceCalculator $itemPriceCalculator)
    {
        $this->itemPriceCalculator = $itemPriceCalculator;
    }

    /**
     * caclucaltes the overall total price of an items array and prices per price group
     *
     * @param $items
     * @param array $groupPrices Will be filled with total prices per group
     *
     * @return int
     */
    public function calculate(
        $items,
        &$groupPrices = array(),
        &$groupedItems = array(),
        $setPrice = false,
        $currency = 'EUR'
    )
    {
        $overallPrice = 0;

        /** @var PriceCalcilationInterface $item */
        foreach ($items as $item) {

            $itemPrice = $this->itemPriceCalculator->calculate($item, $currency);

            // add total-item-price to group
            $this->addPriceToPriceGroup($itemPrice, $item, $groupPrices, $groupedItems);

            // add to overall price
            $overallPrice += $itemPrice;
        }

        return $overallPrice;
    }

    /**
     * adds price to a price-group
     *
     * @param $price
     * @param $item
     * @param $groupPrices
     * @param $groupedItems
     *
     * @internal param $itemPriceGroup
     */
    protected function addPriceToPriceGroup($price, $item, &$groupPrices, &$groupedItems)
    {
        $itemPriceGroup = $item->getCalcPriceGroup();

        if ($itemPriceGroup === null) {
            $itemPriceGroup = 'undefined';
        }

        if (!isset($groupPrices[$itemPriceGroup])) {
            $groupPrices[$itemPriceGroup] = 0;
        }
        $groupPrices[$itemPriceGroup] += $price;

        // add to grouped items
        if (!isset($groupedItems[$itemPriceGroup])) {
            $groupedItems[$itemPriceGroup] = array(
                'items' => array()
            );
            if (method_exists($item, 'getCalcPriceGroupContent') &&
                $content = $item->getCalcPriceGroupContent()
            ) {
                $groupedItems[$itemPriceGroup] = array_merge($content, $groupedItems[$itemPriceGroup]);
            }
        }
        $groupedItems[$itemPriceGroup]['items'][] = $item;
        $groupedItems[$itemPriceGroup]['price'] = $groupPrices[$itemPriceGroup];
        $groupedItems[$itemPriceGroup]['formattedPrice'] = $this->itemPriceCalculator->formatPrice(
            $groupPrices[$itemPriceGroup],
            null
        );
    }
}