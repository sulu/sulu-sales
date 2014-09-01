<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // Dependencies
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle(),
            new \Liip\ThemeBundle\LiipThemeBundle(),

            // Sulu
            new \Sulu\Bundle\CoreBundle\SuluCoreBundle(),
            new \Sulu\Bundle\AdminBundle\SuluAdminBundle(),
            new \Sulu\Bundle\ProductBundle\SuluProductBundle(),
            new \Doctrine\Bundle\PHPCRBundle\DoctrinePHPCRBundle(),
            new \Sulu\Bundle\TestBundle\SuluTestBundle(),
            new \Sulu\Bundle\ContactBundle\SuluContactBundle(),
            new \Sulu\Bundle\TagBundle\SuluTagBundle(),
            new \Sulu\Bundle\Sales\OrderBundle\SuluSalesOrderBundle(),
            new \Sulu\Bundle\Sales\ShippingBundle\SuluSalesShippingBundle(),
            new \Sulu\Bundle\Sales\CoreBundle\SuluSalesCoreBundle(),
            new \Sulu\Bundle\WebsiteBundle\SuluWebsiteBundle(),
            new \Sulu\Bundle\MediaBundle\SuluMediaBundle(),
            new \Sulu\Bundle\CategoryBundle\SuluCategoryBundle(),

        );

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        if (array_key_exists('APP_DB', $GLOBALS) &&
            file_exists(__DIR__ . '/config/config.' . $GLOBALS['APP_DB'] . '.yml')
        ) {
            $loader->load(__DIR__ . '/config/config.' . $GLOBALS['APP_DB'] . '.yml');
        } else {
            $loader->load(__DIR__ . '/config/config.mysql.yml');
        }
    }
}
