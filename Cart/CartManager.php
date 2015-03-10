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
use Sulu\Bundle\Sales\OrderBundle\Api\Order as ApiOrder;
use Sulu\Bundle\Sales\OrderBundle\Entity\Order;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderRepository;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus;
use Sulu\Bundle\Sales\OrderBundle\Order\OrderManager;
use Sulu\Component\Security\Authentication\UserRepositoryInterface;
use Sulu\Component\Persistence\RelationTrait;

class CartManager
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
        OrderManager $orderManager,
        OrderRepository $orderRepository,
        UserRepositoryInterface $userRepository,
        ItemManager $itemManager,
        EntityRepository $orderStatusRepository,
        EntityRepository $orderTypeRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->itemManager = $itemManager;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->orderTypeRepository = $orderTypeRepository;

        // get cart status entity
//        $statusClass = static::$statusClass;
//        $test = $statusClass::STATUS_IN_CART;
        
    }

    /**
     * @param $user
     * @param null $locale
     *
     * @return null|\Sulu\Bundle\Sales\OrderBundle\Entity\Order
     */
    public function getUserCart($user, $locale = null)
    {
        // default locale from user
        $locale = $locale ?: $user->getLocale();

        // get cart
        $cart = $this->orderRepository->findByStatusIdAndUser(
            $locale,
            OrderStatus::STATUS_IN_CART,
            $user
        );

        // user has no cart - create a new one
        if (!$cart) {
            $cart = new Order();
//            $cart->setCreator($user);
//            $cart->setCreator($user);
//            $cart->setCreated(new \DateTime());
//            $cart->setChanged(new \DateTime());
        }

        return new ApiOrder($cart, $locale);
    }
}
