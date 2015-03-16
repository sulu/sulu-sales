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
use Sulu\Bundle\Sales\CoreBundle\Manager\BaseSalesManager;
use Sulu\Bundle\Sales\CoreBundle\Pricing\GroupedItemsPriceCalculatorInterface;
use Sulu\Bundle\Sales\OrderBundle\Api\Order as ApiOrder;
use Sulu\Bundle\Sales\OrderBundle\Entity\Order;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderRepository;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus;
use Sulu\Bundle\Sales\OrderBundle\Order\OrderManager;
use Sulu\Component\Security\Authentication\UserRepositoryInterface;
use Sulu\Component\Persistence\RelationTrait;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CartManager extends BaseSalesManager
{
    use RelationTrait;

    /**
     * TODO: replace by config
     *
     * defines when a cart expires
     */
    const EXPIRY_MONTHS = 2;

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
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var GroupedItemsPriceCalculatorInterface
     */
    private $priceCalculation;

    /**
     * @var string
     */
    private $defaultCurrency;

    /**
     * @param ObjectManager $em
     * @param SessionInterface $session
     * @param OrderRepository $orderRepository
     * @param OrderManager $orderManager
     * @param GroupedItemsPriceCalculatorInterface $priceCalculation
     */
    public function __construct(
        ObjectManager $em,
        SessionInterface $session,
        OrderRepository $orderRepository,
        OrderManager $orderManager,
        GroupedItemsPriceCalculatorInterface $priceCalculation,
        $defaultCurrency
    )
    {
        $this->em = $em;
        $this->session = $session;
        $this->orderRepository = $orderRepository;
        $this->orderManager = $orderManager;
        $this->priceCalculation = $priceCalculation;
        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * @param $user
     * @param null $locale
     *
     * @return null|\Sulu\Bundle\Sales\OrderBundle\Api\Order
     */
    public function getUserCart($user = null, $locale = null, $currency = null, $persist = false)
    {
        // cart by session ID
        if (!$user) {
            // TODO: get correct locale
            $locale = 'de';
            $cartArray = $this->findCartBySessionId();
        } else {
            // TODO: check if cart for this sessionId exists and assign it to user

            // default locale from user
            $locale = $locale ?: $user->getLocale();
            // get carts
            $cartArray = $this->findCartByUser($locale, $user);
        }

        // cleanup cart array: remove duplicates and expired carts
        $this->cleanupCartArray($cartArray);

        // check if cart exists
        if ($cartArray && count($cartArray) > 0) {
            // multiple carts found, do a cleanup
            $cart = $cartArray[0];
        } else {
            // user has no cart - return empty one
            $cart = $this->createEmptyCart($user, $persist);
        }

        $currency = $currency ?: $this->defaultCurrency;
        $apiOrder = new ApiOrder($cart, $locale, $currency);

        // TODO: calcualte difference to previous cart

        $items = $apiOrder->getItems();

        // perform price calucaltion
        $prices = $supplierItems = null;
        $totalPrice = $this->priceCalculation->calculate($items, $prices, $supplierItems, true);

        if ($supplierItems) {
            // set grouped items
            $apiOrder->setSupplierItems(array_values($supplierItems));
        }
        // set total price
        $apiOrder->setTotalNetPrice($totalPrice);

        return $apiOrder;
    }

    /**
     * updates the cart
     *
     * @param $data
     * @param $user
     * @param $locale
     * @return null|Order
     * @throws \Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderException
     * @throws \Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderNotFoundException
     */
    public function updateCart($data, $user, $locale)
    {
        $cart = $this->getUserCart($user, $locale);
        $userId = $user ? $user->getId() : null;
        $this->orderManager->save($data, $locale, $userId, $cart->getId());
        
        return $cart;
    }

    /**
     * finds cart by session-id
     *
     * @return array
     */
    private function findCartBySessionId()
    {
        $sessionId = $this->session->getId();
        $cartArray = $this->orderRepository->findBy(
            array(
                'sessionId' => $sessionId,
                'status' => OrderStatus::STATUS_IN_CART
            ),
            array(
                'created' => 'DESC'
            )
        );

        return $cartArray;
    }

    /**
     * finds cart by locale and user
     *
     * @param $locale
     * @param $user
     *
     * @return array|null
     */
    private function findCartByUser($locale, $user)
    {
        $cartArray = $this->orderRepository->findByStatusIdAndUser(
            $locale,
            OrderStatus::STATUS_IN_CART,
            $user
        );

        return $cartArray;
    }

    /**
     * removes all elements from database but the first
     *
     * @param $cartArray
     */
    private function cleanupCartArray(&$cartArray)
    {
        if ($cartArray && count($cartArray) > 0) {
            // handle cartArray count is > 1
            foreach ($cartArray as $index => $cart) {
                // delete expired carts
                if ($cart->getChanged()->getTimestamp() < strtotime(static::EXPIRY_MONTHS . ' months ago')) {
                    $this->em->remove($cart);
                    continue;
                }

                // dont delete first element, since this is the current cart
                if ($index === 0) {
                    continue;
                }
                // remove duplicated carts
                $this->em->remove($cart);
            }
        }
    }

    /**
     * adds a product to cart
     *
     * @param $data
     * @param null $user
     * @param null $locale
     *
     * @return null|Order
     */
    public function addProduct($data, $user = null, $locale = null)
    {
        //TODO: locale
        // get cart
        $cart = $this->getUserCart($user, $locale, null, true);
        // define user-id
        $userId = $user ? $user->getId() : null;
        $this->orderManager->addItem($data, $locale, $userId, $cart);

        return $cart;
    }

    /**
     * @param $itemId
     * @param $data
     * @param null $user
     * @param null $locale
     *
     * @return null|Order
     * @throws ItemNotFoundException
     */
    public function updateItem($itemId, $data, $user = null, $locale = null)
    {
        $cart = $this->getUserCart($user, $locale);
        $userId = $user ? $user->getId() : null;

        $item = $this->orderManager->getOrderItemById($itemId, $cart->getEntity());

        $this->orderManager->updateItem($item, $data, $locale, $userId);

        return $cart;
    }

    /**
     * patches an item in cart
     *
     * @param null $user
     * @param null $locale
     *
     * @return null|Order
     */
    public function removeItem($itemId, $user = null, $locale = null)
    {
        $cart = $this->getUserCart($user, $locale);

        $item = $this->orderManager->getOrderItemById($itemId, $cart->getEntity(), $hasMultiple);

        $this->orderManager->removeItem($item, $cart->getEntity(), !$hasMultiple);

        return $cart;
    }

    /**
     * Function creates an empty cart
     * this means an order with status 'in_cart' is created and all necessary data is set
     *
     * @param $user
     * @param $persist
     * @return Order
     * @throws \Sulu\Component\Rest\Exception\EntityNotFoundException
     */
    protected function createEmptyCart($user, $persist)
    {
        $cart = new Order();
        $cart->setCreator($user);
        $cart->setChanger($user);
        $cart->setCreated(new \DateTime());
        $cart->setChanged(new \DateTime());

        // TODO:
        if ($user) {
            $name = $user->getContact()->getFullName();
        } else {
            $name = 'Anonymous';
        }
        $cart->setCustomerName($name);

        $this->orderManager->convertStatus($cart, OrderStatus::STATUS_IN_CART);

        if ($persist) {
            $this->em->persist($cart);
        }

        return $cart;
    }

    /**
     * returns array containing number of items and total-price
     * array('totalItems', 'totalPrice')
     *
     * @param $user
     * @param $locale
     * @return array
     */
    public function getNumberItemsAndTotalPrice($user, $locale)
    {
        $cart = $this->getUserCart($user, $locale);

        return array(
            'totalItems' => count($cart->getItems()),
            'totalPrice' => $cart->getTotalNetPrice(),
            'totalPriceFormatted' => $cart->getTotalNetPriceFormatted(),
            'currency' => $cart->getCurrency()
        );
    }
}
