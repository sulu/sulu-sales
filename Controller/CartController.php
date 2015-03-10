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

use FOS\RestBundle\Routing\ClassResourceInterface;
use Sulu\Bundle\Sales\OrderBundle\Cart\CartManager;
use Sulu\Bundle\Sales\OrderBundle\Entity\Order;
use Symfony\Component\HttpFoundation\Request;
use Sulu\Component\Rest\RestController;
use Sulu\Component\Security\SecuredControllerInterface;


// TODO controller needs to be moved into shop-bundle

/**
 * Makes orders available through a REST API
 *
 * @package Sulu\Bundle\Sales\OrderBundle\Controller
 */
class CartController extends RestController implements ClassResourceInterface, SecuredControllerInterface
{

    protected static $orderEntityName = 'SuluSalesOrderBundle:Order';
    protected static $orderStatusEntityName = 'SuluSalesOrderBundle:OrderStatus';

    protected static $entityKey = 'cart';

    /**
     * @return CartManager
     */
    private function getManager()
    {
        return $this->get('sulu_sales_order.cart_manager');
    }

    /**
     * Retrieves and shows an order with the given ID
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param integer $id order ID
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction(Request $request)
    {
        $manager = $this->getManager();

        $cart = $manager->getUserCart($this->getUser(), $this->getUser()->getLocale());
        
        $view = $this->view($cart, 200);

        $view->setSerializationContext(
            SerializationContext::create()->setGroups(
                'cart'
            )
        );

        return $this->handleView($view);
    }

    /**
     * {@inheritDoc}
     */
    public function getSecurityContext()
    {
        return 'sulu.sales_order.orders';
    }
}
