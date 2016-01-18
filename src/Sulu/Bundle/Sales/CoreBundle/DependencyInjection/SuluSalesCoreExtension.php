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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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

        $container->setParameter(
            'sulu_sales_core.email_from',
            $config['email_from']
        );

        $container->setParameter(
            'sulu_sales_core.shop_location',
            $config['shop_location']
        );

        $container->setParameter(
            'sulu_sales_core.priceformatter_digits',
            $config['priceformatter_digits']
        );

        $this->setParameters($container, 'sulu_sales_core.email_templates', $config['email_templates']);
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
                    'shippings' => 'sales/orders/edit:[id]/shippings/add',
                    'pdf' => 'admin/order/pdf/order-confirmation/[id]'
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

    /**
     * Sets parameters to container as specified by key value pair in params-array
     *
     * @param ContainerBuilder $container
     * @param string $basicPath
     * @param array $paramsArray
     */
    private function setParameters(ContainerBuilder $container, $basicPath, $paramsArray)
    {
        foreach ($paramsArray as $key => $params) {
            $container->setParameter(
                $basicPath . '.' . $key,
                $paramsArray[$key]
            );
        }
    }
}
