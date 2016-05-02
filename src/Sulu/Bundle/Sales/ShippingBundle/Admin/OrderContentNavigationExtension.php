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
    /**
     * {@inheritdoc}
     */
    public function getNavigationItems(array $options = [])
    {
        $shippings = new ContentNavigationItem('salesshipping.shippings.title');
        $shippings->setAction('shippings');
        $shippings->setPosition(20);
        $shippings->setComponent('shippings@sulusalesshipping');
        $shippings->setComponentOptions(['display'=>'orderList']);
        $shippings->setDisplay(['edit']);

        return [$shippings];
    }
}
