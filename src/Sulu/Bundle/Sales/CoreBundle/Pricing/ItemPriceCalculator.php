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
 * Calculates price of an item.
 */
class ItemPriceCalculator
{
    /**
     * @var ProductPriceManagerInterface
     */
    protected $priceManager;

    /**
     * @var string
     */
    protected $defaultLocale;

    /**
     * @param ProductPriceManagerInterface $priceManager
     * @param string $defaultLocale
     */
    public function __construct(
        ProductPriceManagerInterface $priceManager,
        $defaultLocale
    ) {
        $this->priceManager = $priceManager;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Calculates the overall total price of an item.
     *
     * @param CalculableBulkPriceItemInterface $item
     * @param string|null $currency
     * @param bool|null $useProductsPrice
     *
     * @throws PriceCalculationException
     *
     * @return float
     */
    public function calculate($item, $currency = null, $useProductsPrice = true)
    {
        $priceValue = $this->getItemPrice($item, $currency, $useProductsPrice);

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
     * Returns price of a single item based on it's quantity.
     *
     * @param CalculableBulkPriceItemInterface $item
     * @param null|string $currency
     * @param null|bool $useProductsPrice
     *
     * @return float
     */
    public function getItemPrice($item, $currency = null, $useProductsPrice = null)
    {
        $currency = $this->getCurrency($currency);

        if ($useProductsPrice === null) {
            $useProductsPrice = $item->getUseProductsPrice();
        }

        // validate item
        $this->validateItem($item);

        $priceValue = $item->getPrice();

        if ($useProductsPrice) {
            $priceValue = $this->getValidProductPriceForItem($item, $currency);
        }

        return $priceValue;
    }

    /**
     * Format price.
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
     * Validate item values.
     *
     * @param CalculableBulkPriceItemInterface $item
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
     * Throws an exception if value is null.
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
     * Either returns currency or default currency.
     *
     * @param string $currency
     *
     * @return string
     */
    private function getCurrency($currency)
    {
        return $currency ?: $this->defaultLocale;
    }

    /**
     * Returns the valid product price for item.
     *
     * @param CalculableBulkPriceItemInterface $item
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
        if (!empty($specialPriceValue) && !empty($bulkPriceValue)) {
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
}
