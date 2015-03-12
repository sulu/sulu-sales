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

use Sulu\Bundle\Sales\CoreBundle\Pricing\Exceptions\PriceCalculationException;

/**
 * Calculate Price of an Order
 */
class GroupedItemPriceCalculator implements GroupedItemsPriceCalculatorInterface
{
    /**
     * caclucaltes the overall total price of an items array and prices per price group
     *
     * @param $items
     * @param array $groupPrices Will be filled with total prices per group
     *
     * @return int
     */
    public function calculate($items, &$groupPrices = array(), &$groupedItems = array(), $setPrice = false)
    {
        $overallPrice = 0;

        /** @var PriceCalcilationInterface $item */
        foreach ($items as $item) {
            // validate item
            $this->validateItem($item);

            // TODO: item-price calculation more modular
            
            // get items price
            $itemPrice = $item->getCalcPrice() * $item->getCalcQuantity();

            // calculate items discount
            $discount = ($itemPrice / 100) * $item->getCalcDiscount();

            // calculate total item price
            $totalItemPrice = $itemPrice - $discount;

            // add total-item-price to group
            $this->addPriceToPriceGroup($totalItemPrice, $item, $groupPrices, $groupedItems);

            // set price on item
            if ($setPrice && method_exists($item, 'setPrice')) {
                $item->setPrice($totalItemPrice);
            }

            // add to overall price
            $overallPrice += $totalItemPrice;
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

        if ($itemPriceGroup !== null) {
            if (!isset($groupPrices[$itemPriceGroup])) {
                $groupPrices[$itemPriceGroup] = 0;
            }
            $groupPrices[$itemPriceGroup] += $price;
        } else {
            $itemPriceGroup = 'undefined';
        }
        // add to grouped items
        if (!isset($groupedItems[$itemPriceGroup])) {
            $groupedItems[$itemPriceGroup] = array(
                'items' => array()
            );
            if (method_exists($item, 'getCalcPriceGroupContent') &&
                $content = $item->getCalcPriceGroupContent()) {
                $groupedItems[$itemPriceGroup] = array_merge($content, $groupedItems[$itemPriceGroup]);
            }
        }
        $groupedItems[$itemPriceGroup]['items'][] = $item;
        $groupedItems[$itemPriceGroup]['price'] = $groupPrices[$itemPriceGroup];
    }

    /**
     * validate item values
     *
     * @param $item
     *
     * @throws PriceCalculationException
     */
    protected function validateItem($item)
    {
        // item must be instance of PriceCalcualtionInterface
        if (!($item instanceof PriceCalculationItemInterface)) {
            throw new PriceCalculationException('Not an instance of PriceCalculationInterface');
        }

        // validate not null
        $this->validateNotNull('price', $item->getCalcPrice());
        $this->validateNotNull('quantity', $item->getCalcQuantity());

        // validate discount
        $discountPercent = $item->getCalcDiscount();
        if ($discountPercent < 0 || $discountPercent > 100) {
            throw new PriceCalculationException('Discount must be within 0 and 100 percent');
        }
    }

    /**
     * throws an exception if value is null
     *
     * @param $key
     * @param $value
     *
     * @throws PriceCalculationException
     */
    protected function validateNotNull($key, $value)
    {
        if ($value === null) {
            throw new PriceCalculationException('Attribute ' . $key . ' must not be null');
        }
    }
}