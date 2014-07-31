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

class SuluContactContentNavigation extends ContentNavigation
{

    public function __construct()
    {
        parent::__construct();

        $this->setName('SalesOrder');

        /* CONTACTS */
        // details
        $details = new NavigationItem('content-navigation.sales.order.overview');
        $details->setAction('overview');
        $details->setContentType('order');
        $details->setContentComponent('orders@sulusalesorder');
        $details->setContentComponentOptions(array('display'=>'form'));
        $this->addNavigationItem($details);

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
