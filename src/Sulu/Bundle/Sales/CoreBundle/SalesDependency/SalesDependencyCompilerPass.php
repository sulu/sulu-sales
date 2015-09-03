<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\SalesDependency;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Add all services with the given tag to the services dependency class
 *
 * @package Sulu\Bundle\AdminBundle\DependencyInjection\Compiler
 */
abstract class SalesDependencyCompilerPass implements CompilerPassInterface
{
    protected $tagName;
    protected $serviceName;

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (null !== $this->tagName && null !== $this->serviceName) {
            $dependencyManager = $container->getDefinition($this->serviceName);

            $taggedServices = $container->findTaggedServiceIds($this->tagName);

            foreach ($taggedServices as $id => $attributes) {

                $dependencyClass = $container->getDefinition($id);

                $dependencyManager->addMethodCall('addDependencyClass', array($dependencyClass));
            }
        }
    }
}
