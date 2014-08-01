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

use Sulu\Bundle\AdminBundle\Admin\ContentNavigation;
use Sulu\Bundle\AdminBundle\Navigation\NavigationItem;

class SuluSalesOrderContentNavigation extends ContentNavigation
{

    public function __construct()
    {
        parent::__construct();

        $this->setName('SalesOrder');

        /* CONTACTS */
        // details
        $overview = new NavigationItem('public.overview');
        $overview->setAction('overview');
        $overview->setContentType('order');
        $overview->setContentComponent('orders@sulusalesorder');
        $overview->setContentComponentOptions(array('display'=>'form'));
        $this->addNavigationItem($overview);

//        // activities
//        $activities = new NavigationItem('content-navigation.sales.order.');
//        $activities->setAction('activities');
//        $activities->setContentType('contact');
//        $activities->setContentComponent('contacts@sulucontact');
//        $activities->setContentComponentOptions(array('display'=>'activities'));
//        $activities->setContentDisplay(array('edit'));
//        $this->addNavigationItem($activities);

    }

    private function getViewForAccount() {

    }
}
