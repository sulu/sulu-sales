<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\DependencyInjection\Compiler;

use Sulu\Bundle\Sales\CoreBundle\SalesDependency\SalesDependencyCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class AddOrderDependencyCompilerPass
 * compiler pass for order dependencies
 *
 * @package Sulu\Bundle\Sales\OrderBundle\DependencyInjection\Compiler
 */
class AddOrderDependencyCompilerPass extends SalesDependencyCompilerPass
{
    public function __construct() {
        $this->tagName = 'sulu.sales_order.order_dependency';
        $this->serviceName = 'sulu_sales_order.order_dependency_manager';
    }
}
