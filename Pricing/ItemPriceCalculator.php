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
class ItemPriceCalculator 
{
    protected $priceManager;
    protected $defaultLocale;

    public function __construct(
        ProductPriceManagerInterface $priceManager,
        $defaultLocale
    )
    {
        $this->priceManager = $priceManager;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * caclucaltes the overall total price of an item
     * @param $item
     * @param string $currency
     * @return int
     * @throws PriceCalculationException
     */
    public function calculate($item, $currency = null, $useProductsPrice = true)
    {
        $currency = $this->getCurrency($currency);

        // validate item
        $this->validateItem($item);

        // get bulk price
        if ($useProductsPrice) {
            $product = $item->getCalcProduct();
            $price = $this->priceManager->getBulkPriceForCurrency($product, $item->getCalcQuantity(), $currency);
            $price = $price->getPrice();
        } else {
            $price = $item->getPrice();
        }
        $this->validateNotNull('price', $price);

        $itemPrice = $price * $item->getCalcQuantity();

        // calculate items discount
        $discount = ($itemPrice / 100) * $item->getCalcDiscount();

        // calculate total item price
        $totalPrice = $itemPrice - $discount;

        return $totalPrice;
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
        // validate not null
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

    /**
     * format price
     *
     * @param $price
     * @param $currency
     * @param string $locale
     * @return String
     */
    public function formatPrice($price, $currency, $locale = 'de')
    {
        return $this->priceManager->getFormattedPrice($price, $currency, $locale);
    }

    /**
     * format price
     *
     * @param $price
     * @param $currency
     * @param string $locale
     * @return String
     */
    public function getItemPrice($item, $currency, $useProductPrice = true)
    {
        $currency = $this->getCurrency($currency);
        
        if ($useProductPrice) {
            $product = $item->getCalcProduct();
            $price = $this->priceManager->getBulkPriceForCurrency($product, $item->getCalcQuantity(), $currency);
            $price = $price->getPrice();
        } else {
            $price = $item->getPrice();
        }
        
        return $price;
    }

    /**
     * either returns currency or default currency
     *
     * @param $currency
     * @return mixed
     */
    private function getCurrency($currency)
    {
        return $currency?: $this->defaultLocale;
        
    }
}