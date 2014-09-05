<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\Controller;

use Sulu\Bundle\AdminBundle\Admin\ContentNavigation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class NavigationController
 * @package Sulu\Bundle\Sales\OrderBundle\Controller
 */
class NavigationController extends Controller
{

    const SERVICE_NAME = 'sulu_sales_order.admin.content_navigation';

    /**
     * returns content navigation for an order
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function orderAction()
    {
        $data = array();
        /** @var ContentNavigation $contentNavigation */
        if ($this->has(self::SERVICE_NAME)) {
            $contentNavigation = $this->get(self::SERVICE_NAME);
            $data = $contentNavigation->toArray('order');
        }

        return new Response(json_encode($data));
    }
}
