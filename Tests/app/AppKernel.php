<?php

use Sulu\Bundle\TestBundle\Kernel\SuluTestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends SuluTestKernel
{

    public function registerBundles()
    {
        $bundles = parent::registerBundles();
        $extraBundles = array(
            new \Sulu\Bundle\Sales\OrderBundle\SuluSalesOrderBundle(),
            new \Sulu\Bundle\Sales\CoreBundle\SuluSalesCoreBundle(),
            new \Sulu\Bundle\ProductBundle\SuluProductBundle(),
            // test mails
            new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            // test pdf
            new Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
            new Massive\Bundle\PdfBundle\MassivePdfBundle(),
        );

        $bundles = array_merge($bundles, $extraBundles);
        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        parent::registerContainerConfiguration($loader);
        $loader->load(__DIR__ . '/config/config.yml');
    }
}
