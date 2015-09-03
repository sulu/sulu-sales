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

class SuluSalesShippingContentNavigation implements ContentNavigationProviderInterface
{

    public function getNavigationItems(array $options = array())
    {
        $overview = new ContentNavigationItem('public.details');
        $overview->setAction('overview');
        $overview->setComponent('shippings@sulusalesshipping');
        $overview->setComponentOptions(array('display'=>'form'));

        return array($overview);
    }
}
