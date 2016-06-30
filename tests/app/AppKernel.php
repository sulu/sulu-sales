<?php

/*
 * This file is part of the Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Sulu\Bundle\TestBundle\Kernel\SuluTestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends SuluTestKernel
{
    public function registerBundles()
    {
        $bundles = parent::registerBundles();

        $bundles = array_merge(
            $bundles,
            [
                // Sales bundles.
                new Sulu\Bundle\Sales\CoreBundle\SuluSalesCoreBundle(),
                new Sulu\Bundle\Sales\OrderBundle\SuluSalesOrderBundle(),
                new Sulu\Bundle\Sales\ShippingBundle\SuluSalesShippingBundle(),

                // Sulu dependencies.
                new Sulu\Bundle\ProductBundle\SuluProductBundle(),
                new Sulu\Bundle\ContactExtensionBundle\SuluContactExtensionBundle(),
                new Sulu\Bundle\PricingBundle\SuluPricingBundle(),

                // Mailer dependencies.
                new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),

                // Pdf dependencies.
                new Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
                new Massive\Bundle\PdfBundle\MassivePdfBundle(),
            ]
        );

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        parent::registerContainerConfiguration($loader);

        $loader->load(__DIR__ . '/config/config.yml');
    }
}
