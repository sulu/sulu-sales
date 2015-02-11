<?php

use Sulu\Bundle\TestBundle\Kernel\SuluTestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends SuluTestKernel
{
    public function registerBundles()
    {
        $bundles = parent::registerBundles();
//        $bundles = array(
//            // Dependencies
//            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
//            new Symfony\Bundle\TwigBundle\TwigBundle(),
//            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
//            new Symfony\Bundle\MonologBundle\MonologBundle(),
//            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
//            new JMS\SerializerBundle\JMSSerializerBundle(),
//            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
//            new FOS\RestBundle\FOSRestBundle(),
//            new Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle(),
//            new \Liip\ThemeBundle\LiipThemeBundle(),
//
//            // Sulu
//            new \Sulu\Bundle\CoreBundle\SuluCoreBundle(),
//            new \Sulu\Bundle\AdminBundle\SuluAdminBundle(),
//            new \Sulu\Bundle\ProductBundle\SuluProductBundle(),
//            new \Doctrine\Bundle\PHPCRBundle\DoctrinePHPCRBundle(),
//            new \Sulu\Bundle\TestBundle\SuluTestBundle(),
//            new \Sulu\Bundle\ContactBundle\SuluContactBundle(),
//            new \Sulu\Bundle\TagBundle\SuluTagBundle(),
//            new \Sulu\Bundle\Sales\CoreBundle\SuluSalesCoreBundle(),
//            new \Sulu\Bundle\WebsiteBundle\SuluWebsiteBundle(),
//        );

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        parent::registerContainerConfiguration($loader);
        $loader->load(__DIR__ . '/config/config.yml');
    }
}
