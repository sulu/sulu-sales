<?php

namespace Sulu\Bundle\Sales\ShippingBundle\Admin;

use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationInterface;
use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationItem;

class OrderContentNavigationExtension implements ContentNavigationInterface
{
    private $navigation = array();

    public function __construct()
    {
        $shippings = new ContentNavigationItem('salesshipping.shippings.title');
        $shippings->setAction('shippings');
        $shippings->setComponent('shippings@sulusalesshipping');
        $shippings->setComponentOptions(array('display'=>'orderList'));
        $shippings->setDisplay(array('edit'));
        $shippings->setGroups(array('order'));

        $this->navigation[] = $shippings;
    }

    public function getNavigationItems()
    {
        return $this->navigation;
    }
}
