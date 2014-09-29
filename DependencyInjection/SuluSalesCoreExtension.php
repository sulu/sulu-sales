<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SuluSalesCoreExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $this->setDefaultRoutes($config);
        $container->setParameter(
            'sulu_sales_core.routes',
            $config['routes']
        );
    }

    /**
     * Sets default values for routes for order/shipping/invoice bundle
     *
     * @param $config
     */
    private function setDefaultRoutes(&$config)
    {
        if (!array_key_exists('routes', $config) || count($config['routes']) == 0) {
            $config['routes'] = array(
                'order' => array(
                    'base' => 'sales/orders',
                    'details' => 'sales/orders/edit:[id]/details',
                    'add' => 'sales/orders/edit:[id]/add',
                    'shippings' => 'sales/orders/edit:[id]/shippings/add'
                ),
                'shipping' => array(
                    'base' => 'sales/shippings',
                    'details' => 'sales/shippings/edit:[id]/details',
                    'add' => 'sales/shippings/edit:[id]/add'
                ),
                'invoice' => array(
                    'base' => 'sales/invoices',
                    'details' => 'sales/invoices/edit:[id]/details',
                    'add' => 'sales/invoices/edit:[id]/add'
                )
            );
        }
    }
}
