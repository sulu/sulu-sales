<?php

use Sulu\Bundle\TestBundle\Kernel\SuluTestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends SuluTestKernel
{
    public function registerBundles()
    {
        $allBundles = parent::registerBundles();
        $bundles = [
                new \Sulu\Bundle\ProductBundle\SuluProductBundle(),
                new \Sulu\Bundle\Sales\CoreBundle\SuluSalesCoreBundle(),

                new Sulu\Bundle\ContactExtensionBundle\SuluContactExtensionBundle(),

                // test mails
                new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            ];

        return array_merge($allBundles, $bundles);
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        parent::registerContainerConfiguration($loader);
        $loader->load(__DIR__ . '/config/config.yml');
    }
}
