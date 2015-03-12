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

interface CalculablePriceItemInterface
{
    /**
     * returns price of item
     * 
     * @return float
     */
    public function getCalcPrice();
    
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
}