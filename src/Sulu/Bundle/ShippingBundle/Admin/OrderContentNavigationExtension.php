<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */


namespace Sulu\Bundle\Sales\ShippingBundle\Admin;

use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationItem;
use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationProviderInterface;

class OrderContentNavigationExtension implements ContentNavigationProviderInterface
{
    public function getNavigationItems(array $options = array())
    {
        $shippings = new ContentNavigationItem('salesshipping.shippings.title');
        $shippings->setAction('shippings');
        $shippings->setComponent('shippings@sulusalesshipping');
        $shippings->setComponentOptions(array('display'=>'orderList'));
        $shippings->setDisplay(array('edit'));

        return array($shippings);
    }
}
