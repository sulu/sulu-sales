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

interface GroupedItemsPriceCalculatorInterface
{
    /**
     * calculate price of items array
     *
     * @param array $items Array with PriceCalculationItems
     * @param array $groupPrices Prices grouped by priceGroup
     * @param array $groupedItems Will be filled with items and prices
     * @param string $currencyCode The currency for which the price should be calculated
     *
     * @return float total-price of all items
     */
    public function calculate($items, &$groupPrices = array(), &$groupedItems = array(), $currencyCode = 'EUR');

    /**
     * Sets all item prices to the changed prices
     * Note: This will only work, if price changes have been calculated before. Since this is a service which can be
     *       called multiple times with many different items.
     *
     * @param $items
     * @return bool If prices have changed
     */
    public function setPricesOfChanged($items);
}
