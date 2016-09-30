<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle;

use Sulu\Bundle\PersistenceBundle\PersistenceBundleTrait;
use Sulu\Bundle\Sales\OrderBundle\DependencyInjection\Compiler\AddOrderDependencyCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SuluSalesOrderBundle extends Bundle
{
    use PersistenceBundleTrait;

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddOrderDependencyCompilerPass);

        $this->buildPersistence(
            [
                'Sulu\Bundle\Sales\OrderBundle\Entity\OrderInterface' => 'sulu.model.sales_order.class',
            ],
            $container
        );
    }
}
