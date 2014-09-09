<?php

namespace Sulu\Bundle\Sales\ShippingBundle\Admin;

use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationInterface;
use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationItem;

class OrderContentNavigationExtension implements ContentNavigationInterface
{
    private $navigation = array();

    public function __construct()
    {
        $permissions = new ContentNavigationItem('salesshipping.shippings.title');
        $permissions->setAction('shippings');
        $permissions->setComponent('shippings@sulusalesshipping');
        $permissions->setComponentOptions(array('display'=>'orderList'));
        $permissions->setDisplay(array('edit'));
        $permissions->setGroups(array('order'));

        $this->navigation[] = $permissions;
    }

    public function getNavigationItems()
    {
        return $this->navigation;
    }
}
