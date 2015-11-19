<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\Tests\Functional\Pricing;

use Sulu\Bundle\Sales\CoreBundle\Pricing\PriceFormatter;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

/**
 * Tests for PriceFormatter.
 */
class PriceFormatterTest extends SuluTestCase
{
    /**
     * @var PriceFormatter
     */
    protected $priceFormatter;

    protected function setUp()
    {
        parent::setUp();

        $defaultLocale = $this->getContainer()->getParameter('website_locale');
        $defaultDigits = $this->getContainer()->getParameter('priceformatter_digits');
        $this->priceFormatter = new PriceFormatter($defaultLocale, $defaultDigits);
    }

    /**
     * Test format with locale but without currency.
     */
    public function testFormat()
    {
        // test de-AT
        $formatted = $this->priceFormatter->format(123.45, 3, 'de-AT');

        $this->assertEquals('123,450', $formatted, 'price format for "de-AT" does not match');

        // test en-US
        $formatted = $this->priceFormatter->format(123.45, 2, 'en-US');

        $this->assertEquals('123.45', $formatted, 'price format for "en-US" does not match');
    }

    /**
     * Test format with default locale and default digits.
     */
    public function testFormatWithDefaults()
    {
        // test defaultLocale with 3 digits
        $formatted = $this->priceFormatter->format(123.45, 3);

        $this->assertEquals('123,450', $formatted, 'price format for defaultLocale does not match');

        // test defaultLocale with default amount of digits (see self::setUp)
        $formatted = $this->priceFormatter->format(123.45);

        $this->assertEquals('123,45', $formatted, 'price format for default digits does not match');
    }

    /**
     * Test format with currency and locale.
     */
    public function testFormatWithCurrency()
    {
        // test de-AT with currency € as prefix
        $formatted = $this->priceFormatter->format(123.45, 3, 'de-AT', '€', PriceFormatter::CURRENCY_LOCATION_PREFIX);

        $this->assertEquals('€ 123,450', $formatted, 'price format with currency for "de-AT" does not match');

        // test en-US with currency $ as suffix
        $formatted = $this->priceFormatter->format(123.45, 2, 'en-US', '$', PriceFormatter::CURRENCY_LOCATION_SUFFIX);

        $this->assertEquals('123.45 $', $formatted, 'price format with currency for "en-US" does not match');


        // test en-US with currency 'null' as suffix - should throw exception
        $this->setExpectedException('Sulu\Bundle\Sales\CoreBundle\Pricing\Exceptions\PriceFormatterException');

        $this->priceFormatter->format(123.45, 2, 'en-US', null, PriceFormatter::CURRENCY_LOCATION_SUFFIX);
    }
}
