<?php

/*
 * This file is part of the Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\Traits;

use Sulu\Bundle\ProductBundle\Entity\TaxClass;
use Sulu\Bundle\ProductBundle\Entity\Unit;
use Sulu\Bundle\ProductBundle\Api\Currency;

/**
 * Helper Trait for getting data that's needed for displaying full item-table
 */
trait ItemTableTrait
{
    /**
     * Returns all currencies.
     *
     * @param string $locale
     *
     * @return array
     */
    private function getCurrencies($locale)
    {
        /** @var Currency[] $currencies */
        $currencies = $this->get('sulu_product.currency_manager')->findAll($locale);

        $currencyValues = array();

        foreach ($currencies as $currency) {
            $currencyValues[] = array(
                'id' => $currency->getId(),
                'name' => $currency->getName(),
                'code' => $currency->getCode()
            );
        }

        return $currencyValues;
    }

    /**
     * Returns all tax classes.
     *
     * @param string $locale
     *
     * @return array
     */
    private function getTaxClasses($locale)
    {
        $itemManager = $this->get('sulu_sales_core.item_manager');
        /** @var TaxClass[] $taxClasses */
        $taxClasses = $this->get('sulu_product.tax_class_repository')->findAll();

        $result = [];

        foreach ($taxClasses as $taxClass) {
            $result[] = [
                'id' => $taxClass->getId(),
                'name' => $taxClass->getTranslation($locale)->getName(),
                'tax' => $itemManager->retrieveTaxForClass($taxClass)
            ];
        }

        return $result;
    }

    /**
     * Returns all product units.
     *
     * @param string $locale
     *
     * @return array
     */
    private function getProductUnits($locale)
    {
        /** @var Unit[] $productUnits */
        $productUnits = $this->get('sulu_product.unit_repository')->findAll();

        $result = [];

        foreach ($productUnits as $productUnit) {
            $result[] = [
                'id' => $productUnit->getId(),
                'name' => $productUnit->getTranslation($locale)->getName(),
            ];
        }

        return $result;
    }
}
