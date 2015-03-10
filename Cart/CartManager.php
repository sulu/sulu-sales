<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\Cart;

use Doctrine\Common\Persistence\ObjectManager;

use Doctrine\ORM\EntityRepository;
use Sulu\Bundle\Sales\CoreBundle\Item\ItemManager;
use Sulu\Bundle\Sales\CoreBundle\Manager\BaseSalesManager;
use Sulu\Bundle\Sales\OrderBundle\Api\Order as ApiOrder;
use Sulu\Bundle\Sales\OrderBundle\Entity\Order;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderRepository;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus;
use Sulu\Bundle\Sales\OrderBundle\Order\OrderManager;
use Sulu\Component\Security\Authentication\UserRepositoryInterface;
use Sulu\Component\Persistence\RelationTrait;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartManager extends BaseSalesManager
{
    use RelationTrait;

    protected static $orderEntityName = 'SuluSalesOrderBundle:Order';
    protected static $contactEntityName = 'SuluContactBundle:Contact';
    protected static $addressEntityName = 'SuluContactBundle:Address';
    protected static $accountEntityName = 'SuluContactBundle:Account';
    protected static $orderStatusEntityName = 'SuluSalesOrderBundle:OrderStatus';
    protected static $orderTypeEntityName = 'SuluSalesOrderBundle:OrderType';
    protected static $orderTypeTranslationEntityName = 'SuluSalesOrderBundle:OrderTypeTranslation';
    protected static $orderAddressEntityName = 'SuluSalesOrderBundle:OrderAddress';
    protected static $orderStatusTranslationEntityName = 'SuluSalesOrderBundle:OrderStatusTranslation';
    protected static $itemEntityName = 'SuluSalesCoreBundle:Item';
    protected static $termsOfDeliveryEntityName = 'SuluContactBundle:TermsOfDelivery';
    protected static $termsOfPaymentEntityName = 'SuluContactBundle:TermsOfPayment';
    protected static $statusClass = 'Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus';

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var OrderManager
     */
    private $orderManager;

    /**
     * @var ItemManager
     */
    private $itemManager;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var EntityRepository
     */
    private $orderTypeRepository;

    /**
     * @var EntityRepository
     */
    private $orderStatusRepository;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * constructor
     *
     * @param ObjectManager $em
     * @param OrderManager $orderManager
     * @param OrderRepository $orderRepository
     * @param UserRepositoryInterface $userRepository
     * @param ItemManager $itemManager
     * @param EntityRepository $orderStatusRepository
     * @param EntityRepository $orderTypeRepository
     */
    public function __construct(
        ObjectManager $em,
        SessionInterface $session,
        OrderRepository $orderRepository,
        OrderManager $orderManager,
        UserRepositoryInterface $userRepository,
        ItemManager $itemManager,
        EntityRepository $orderStatusRepository,
        EntityRepository $orderTypeRepository
    ) {
        $this->em = $em;
        $this->session = $session;
        $this->orderRepository = $orderRepository;
        $this->orderManager = $orderManager; //FIXME: unused
        $this->userRepository = $userRepository; //FIXME: unused
        $this->itemManager = $itemManager; //FIXME: unused
        $this->orderStatusRepository = $orderStatusRepository; //FIXME: unused
        $this->orderTypeRepository = $orderTypeRepository; //FIXME: unused
    }

    /**
     * @param $user
     * @param null $locale
     *
     * @return null|\Sulu\Bundle\Sales\OrderBundle\Entity\Order
     */
    public function getUserCart($user = null, $locale = null)
    {
        // TODO: add management of expired CART
        
        // TODO: cleanup of expired carts
        
        // cart by session ID
        if (!$user) {
            $sessionId = $this->session->getId();
            $this->orderRepository->findBy(array('sessionId' => $sessionId));
        }

        // default locale from user
        $locale = $locale ?: $user->getLocale();

        // get cart // TODO: move this to orderManager?
        $cartArray = $this->orderRepository->findByStatusIdAndUser(
            $locale,
            OrderStatus::STATUS_IN_CART,
            $user
        );
        
        // TODO: handle when cartArray count is > 1

        // user has no cart - return empty one
        if (!$cartArray) {
            $cart = new Order();
        }

        return new ApiOrder($cart, $locale);
    }
    
    public function addProduct($data, $user = null, $locale = null)
    {
        $cart = $this->getUserCart($user, $locale);



//        $cart->addItem();
    }
    
//    protected function checkProductData
}
