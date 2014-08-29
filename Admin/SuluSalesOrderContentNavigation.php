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

use Sulu\Bundle\AdminBundle\Navigation\ContentNavigation;
use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationItem;

class SuluSalesOrderContentNavigation extends ContentNavigation
{

    public function __construct()
    {
        parent::__construct();

        $this->setName('SalesOrder');

        /* CONTACTS */
        // details
        $overview = new ContentNavigationItem('public.overview');
        $overview->setAction('overview');
        $overview->setGroups(array('order'));
        $overview->setComponent('orders@sulusalesorder');
        $overview->setComponentOptions(array('display'=>'form'));
        $this->addNavigationItem($overview);
    }
}
