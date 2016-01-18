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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('sulu_sales_core')
            ->children()
                ->scalarNode('priceformatter_digits')->defaultValue(2)->end()
                ->arrayNode('routes')
                    ->useAttributeAsKey('title')
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('base')->end()
                            ->scalarNode('details')->end()
                            ->scalarNode('add')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('email_templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('footer_txt')->defaultValue('')->end()
                        ->scalarNode('footer_html')->defaultValue('')->end()
                    ->end()
                ->end()
                ->scalarNode('email_from')->defaultValue('')->end()
                ->scalarNode('shop_location')->defaultValue('')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
