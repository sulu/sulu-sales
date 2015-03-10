<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\Tests\Functional\Manager;

use Massive\Bundle\Purchase\OrderBundle\Entity\OrderRepository;
use Sulu\Bundle\Sales\OrderBundle\Cart\CartManager;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus;
use Sulu\Bundle\Sales\OrderBundle\Order\OrderManager;
use Sulu\Bundle\Sales\OrderBundle\Tests\OrderTestBase;
use Sulu\Bundle\SecurityBundle\Entity\UserRepository;

class CartManagerTest extends OrderTestBase
{
    protected $cartManager;

    protected function setUpTestData()
    {
        parent::setUpTestData();

        // set order to cart order
        $this->orderStatus = $this->em->getRepository(static::$orderStatusEntityName)->find(OrderStatus::STATUS_IN_CART);
        $this->order->setStatus($this->orderStatus);

        $this->em->flush();
    }

    public function testGetCartByUser()
    {
        $cart = $this->getCartManager()->getUserCart($this->user);

        // $cart is an ApiOrder, so get entity first
        $this->assertEquals($cart->getEntity(), $this->order);
    }

    /**
     * @return CartManager
     */
    protected function getCartManager()
    {
        return $this->getContainer()->get('sulu_sales_order.cart_manager');
    }
}
