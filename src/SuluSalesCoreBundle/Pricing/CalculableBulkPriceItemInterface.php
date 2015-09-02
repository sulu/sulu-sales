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

interface CalculableBulkPriceItemInterface
{
    /**
     * returns price of item for a certain quantity
     * 
     * @return float
     */
    public function getCalcProduct();

    /**
     * returns quantity of items
     *
     * @return float
     */
    public function getCalcQuantity();

    /**
     * returns discount in percent of an item
     *
     * @return float from 0 to 100
     */
    public function getCalcDiscount();

    /**
     * returns the currency of an item
     *
     * @return string
     */
    public function getCalcCurrencyCode();

    /**
     * get items current price
     *
     * @return float
     */
    public function getPrice();

    /**
     * set price-change to item
     */
    public function setPriceChange($from, $to);
}
