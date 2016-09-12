<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\DependencyInjection;

use Sulu\Bundle\PersistenceBundle\DependencyInjection\PersistenceExtensionTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SuluSalesOrderExtension extends Extension
{
    use PersistenceExtensionTrait;

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        // Set pdf templates via helper function.
        $this->setParameters(
            $container,
            'sulu_sales_order.pdf_templates',
            $config['pdf_templates']
        );

        // Set email templates via helper function.
        $this->setParameters(
            $container,
            'sulu_sales_order.email_templates',
            $config['email_templates']
        );

        // Email confirmation settings.
        $shopEmailFrom = null;
        if (isset($config['shop_email_from'])) {
            $shopEmailFrom = $config['shop_email_from'];
        }
        $shopEmailConfirmationTo = null;
        if (isset($config['shop_email_confirmation_to'])) {
            $shopEmailConfirmationTo = $config['shop_email_confirmation_to'];
        }
        $container->setParameter(
            'sulu_sales_order.shop_email_from',
            $shopEmailFrom
        );
        $container->setParameter(
            'sulu_sales_order.shop_email_confirmation_to',
            $shopEmailConfirmationTo
        );
        $container->setParameter(
            'sulu_sales_order.send_email_confirmation_to_customer',
            $config['send_email_confirmation_to_customer']
        );
        $container->setParameter(
            'sulu_sales_order.send_email_confirmation_to_shopowner',
            $config['send_email_confirmation_to_shopowner']
        );
        $container->setParameter(
            'sulu_sales_order.pdf_response_type',
            $config['pdf_response_type']
        );
        $container->setParameter(
            'sulu_sales_order.pdf_order_confirmation_name_prefix',
            $config['pdf_order_confirmation_name_prefix']
        );
        $container->setParameter(
            'sulu_sales_order.pdf_order_dynamically_name_prefix',
            $config['pdf_order_dynamically_name_prefix']
        );

        $this->configurePersistence($config['objects'], $container);
    }

    /**
     * Sets parameters to container as specified by key value pair in params-array.
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
