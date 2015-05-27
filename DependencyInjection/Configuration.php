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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $treeBuilder->root('sulu_sales_order')
            ->children()
            ->arrayNode('pdf_templates')
            ->addDefaultsIfNotSet()
                ->children()
                ->scalarNode('base')->defaultValue('SuluSalesCoreBundle:Pdf:pdf-base.html.twig')->end()
                ->scalarNode('header')->defaultValue('SuluSalesCoreBundle:Pdf:pdf-base-header.html.twig')->end()
                ->scalarNode('footer')->defaultValue('SuluSalesCoreBundle:Pdf:pdf-base-footer.html.twig')->end()
                ->scalarNode('macros')->defaultValue('SuluSalesCoreBundle:Pdf:pdf-macros.html.twig')->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
