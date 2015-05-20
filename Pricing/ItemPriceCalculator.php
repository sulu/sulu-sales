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
     * Returns the valid product price for item
     *
     * @param object $item
     * @param string $currency
     *
     * @return int
     */
    private function getValidProductPriceForItem($item, $currency)
    {
        $product = $item->getCalcProduct();
        $specialPriceValue = null;
        $bulkPriceValue = null;

        //get special price
        $specialPrice = $this->priceManager->getSpecialPriceForCurrency($product, $currency);
        if ($specialPrice) {
            $specialPriceValue = $specialPrice->getPrice();
        }

        //get bulk price
        $bulkPrice = $this->priceManager->getBulkPriceForCurrency($product, $item->getCalcQuantity(), $currency);
        if ($bulkPrice) {
            $bulkPriceValue = $bulkPrice->getPrice();
        }

        //take the smallest
        if(!empty($specialPriceValue) && !empty($bulkPriceValue)) {
            $priceValue = $specialPriceValue;
            if ($specialPriceValue > $bulkPriceValue) {
                $priceValue = $bulkPriceValue;
            }
        } elseif (!empty($specialPriceValue)) {
            $priceValue = $specialPriceValue;
        } else {
            $priceValue = $bulkPriceValue;
        }

        // no price set - return 0
        if (empty($priceValue)) {
            return 0;
        }

        return $priceValue;
    }

    /**
     * Calculates the overall total price of an item
     *
     * @param CalculableBulkPriceItemInterface $item
     * @param string|null $currency
     * @param bool|null $useProductsPrice
     *
     * @return int
     * @throws PriceCalculationException
     */
    public function calculate($item, $currency = null, $useProductsPrice = true)
    {
        $currency = $this->getCurrency($currency);

        // validate item
        $this->validateItem($item);

        if ($useProductsPrice) {
            $priceValue = $this->getValidProductPriceForItem($item, $currency);
        } else {
            $priceValue = $item->getPrice();
        }
        $this->validateNotNull('price', $priceValue);

        if ($item->getPrice() && $item->getPrice() !== $priceValue) {
            $item->setPriceChange($item->getPrice(), $priceValue);
        }

        $itemPrice = $priceValue * $item->getCalcQuantity();

        // calculate items discount
        $discount = ($itemPrice / 100) * $item->getCalcDiscount();

        // calculate total item price
        $totalPrice = $itemPrice - $discount;

        return $totalPrice;
    }

    /**
     * Format price
     *
     * @param float $price
     * @param string $currency
     * @param string $locale
     *
     * @return string
     */
    public function formatPrice($price, $currency, $locale = 'de')
    {
        return $this->priceManager->getFormattedPrice($price, $currency, $locale);
    }

    /**
     * Validate item values
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
     * Throws an exception if value is null
     *
     * @param string $key
     * @param mixed $value
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
     * Format price
     *
     * @param CalculableBulkPriceItemInterface $item
     * @param string $currency
     * @param bool $useProductPrice
     *
     * @return string
     */
    public function getItemPrice($item, $currency, $useProductPrice = true)
    {
        $currency = $this->getCurrency($currency);

        if ($useProductPrice) {
            $priceValue = $this->getValidProductPriceForItem($item, $currency);
         } else {
            $priceValue = $item->getPrice();
        }

        return $priceValue;
    }

    /**
     * Either returns currency or default currency
     *
     * @param string $currency
     * @return mixed
     */
    private function getCurrency($currency)
    {
        return $currency?: $this->defaultLocale;
    }
}
