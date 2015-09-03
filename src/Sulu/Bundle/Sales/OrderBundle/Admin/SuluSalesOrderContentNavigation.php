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

use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationItem;
use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationProviderInterface;

class SuluSalesOrderContentNavigation implements ContentNavigationProviderInterface
{
    public function getNavigationItems(array $options = array())
    {
        /* CONTACTS */
        // details
        $overview = new ContentNavigationItem('public.overview');
        $overview->setAction('overview');
        $overview->setComponent('orders@sulusalesorder');
        $overview->setComponentOptions(array('display'=>'form'));

        return array($overview);
    }
}
