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

use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus;
use Sulu\Bundle\Sales\OrderBundle\Tests\OrderTestBase;
use Sulu\Bundle\SecurityBundle\Entity\UserRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

class CartManagerTest extends OrderTestBase
{
    protected $cartManager;

    protected function setUpTestData()
    {
        parent::setUpTestData();

        // set order to cart order
        $this->orderStatus = $this->em->getRepository(static::$orderStatusEntityName)->find(OrderStatus::STATUS_IN_CART);
        $this->order->setStatus($this->orderStatus);

        $this->order->setSessionId('IamASessionKey');

        $this->em->flush();
    }

    public function testGetCartByUser()
    {
        // get cart by user
        $cart = $this->getCartManager()->getUserCart($this->user);

        // $cart is an ApiOrder, so get entity first
        $this->assertEquals($cart->getEntity(), $this->order);
    }
    
    public function testGetCartBySessionId()
    {
        // TODO: fix mock of session
//        $sessionMock = new MockFileSessionStorage();
//        $sessionMock->setId('IamASessionKey');
//        $session = new Session($sessionMock);
//
//        $cart = $this->getCartManager()->getUserCart();
//        $this->assertEquals($cart->getEntity(), $this->order);

        $this->assertTrue(true);
    }

    public function testPostItem()
    {
        $data = array();

        $client = $this->createAuthenticatedClient();

//        $client->request('POST', '/api/cart/items', $data);
//        $response = json_decode($client->getResponse()->getContent());
//
//        $client->request('GET', '/api/cart/');
//        $response = json_decode($client->getResponse()->getContent());
//        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        // TODO: make assertions
        $this->assertTrue(true);
    }

    /**
     * @return CartManager
     */
    protected function getCartManager()
    {
        return $this->getContainer()->get('sulu_sales_order.cart_manager');
    }
}
