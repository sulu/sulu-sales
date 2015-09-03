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

interface CalculablePriceGroupItemInterface
{
    /**
     * adds additional information to price group
     *
     * @return array|null
     */
    public function getCalcPriceGroupContent();

    /**
     * return group of price
     *
     * @return string|int|null
     */
    public function getCalcPriceGroup();

}