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

use NumberFormatter;
use Sulu\Bundle\Sales\CoreBundle\Pricing\Exceptions\PriceFormatterException;

/**
 * Formats prices
 */
class PriceFormatter
{
    // define constants for lation of currency
    const CURRENCY_LOCATION_PREFIX = 'prefix';
    const CURRENCY_LOCATION_SUFFIX = 'suffix';
    const CURRENCY_LOCATION_NONE = 'none';

    /**
     * @var string
     */
    protected $defaultLocale;

    /**
     * @var int
     */
    protected $defaultDigits;

    public function __construct($defaultLocale, $defaultDigits)
    {
        $this->defaultLocale = $defaultLocale;
        $this->defaultDigits = $defaultDigits;
    }

    /**
     * @param float $price
     * @param int $digits
     * @param string $locale
     * @param string $currency
     * @param string $currencyPosition
     *
     * @throws PriceFormatterException
     *
     * @return string
     */
    public function format(
        $price,
        $digits = null,
        $locale = null,
        $currency = null,
        $currencyPosition = self::CURRENCY_LOCATION_NONE
    ) {
        if (is_null($locale)) {
            $locale = $this->defaultLocale;
        }

        if (is_null($digits)) {
            $digits = $this->defaultDigits;
        }

        $formatter = $this->getFormatter($locale, $digits);
        $formattedPrice = $formatter->format($price);

        if ($currencyPosition != self::CURRENCY_LOCATION_NONE && is_null($currency)) {
            throw new PriceFormatterException('currency must be set, if location is set');
        }

        switch ($currencyPosition) {
            case self::CURRENCY_LOCATION_PREFIX:
                $formattedPrice = $currency . ' ' . $formattedPrice;
                break;
            case self::CURRENCY_LOCATION_SUFFIX:
                $formattedPrice = $formattedPrice . ' ' . $currency;
                break;
            default:
                break;
        }

        return $formattedPrice;
    }

    /**
     * @param string $locale
     * @param int $digits
     *
     * @return NumberFormatter
     */
    protected function getFormatter($locale = 'de-AT', $digits = 2)
    {
        $formatter = new \NumberFormatter($locale, NumberFormatter::DECIMAL);
        $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $digits);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $digits);
        $formatter->setAttribute(NumberFormatter::DECIMAL_ALWAYS_SHOWN, 1);

        return $formatter;
    }
}
