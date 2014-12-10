<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\Admin;

use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Navigation\Navigation;
use Sulu\Bundle\AdminBundle\Navigation\NavigationItem;
use Sulu\Bundle\SecurityBundle\Permission\SecurityCheckerInterface;

class SuluSalesOrderAdmin extends Admin
{
    /**
     * @var SecurityCheckerInterface
     */
    private $securityChecker;

    public function __construct(SecurityCheckerInterface $securityChecker, $title)
    {
        $this->securityChecker = $securityChecker;

        $rootNavigationItem = new NavigationItem($title);
        $section = new NavigationItem('');

        $sales = new NavigationItem('navigation.sales');
        $sales->setIcon('shopping-cart');

        if ($this->securityChecker->hasPermission('sulu.sales_order.orders', 'view')) {
            $order = new NavigationItem('navigation.sales.order', $sales);
            $order->setAction('sales/orders');
        }

        if ($sales->hasChildren()) {
            $section->addChild($sales);
            $rootNavigationItem->addChild($section);
        }

        $this->setNavigation(new Navigation($rootNavigationItem));
    }

    /**
     * {@inheritdoc}
     */
    public function getCommands()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getJsBundleName()
    {
        return 'sulusalesorder';
    }

    /**
     * {@inheritdoc}
     */
    public function getSecurityContexts()
    {
        return array(
            'Sulu' => array(
                'SalesOrder' => array(
                    'sulu.sales_order.orders',
                )
            )
        );
    }
}
