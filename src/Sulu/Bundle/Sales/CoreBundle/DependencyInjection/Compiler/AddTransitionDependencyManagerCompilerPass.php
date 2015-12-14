<?php

namespace Sulu\Bundle\Sales\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddTransitionDependencyManagerCompilerPass implements CompilerPassInterface
{
    protected $tagName = 'sales_core_transition';
    protected $serviceName = 'sulu_sales_core.dependency_manager';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $dependencyManager = $container->findDefinition($this->serviceName);
        $taggedServices = $container->findTaggedServiceIds($this->tagName);

        foreach ($taggedServices as $id => $attributes) {
            $entity = $container->getDefinition($id)->getClass();

            $dependencyManager->addMethodCall('addMapping', [$entity, $attributes[0]]);
        }
    }

}
