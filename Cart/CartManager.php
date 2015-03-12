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
use Sulu\Bundle\Sales\CoreBundle\Pricing\PriceCalculationInterface;
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
     * @var PriceCalculationInterface
     */
    private $priceCalculation;

    /**
     * @param ObjectManager $em
     * @param SessionInterface $session
     * @param OrderRepository $orderRepository
     * @param OrderManager $orderManager
     */
    public function __construct(
        ObjectManager $em,
        SessionInterface $session,
        OrderRepository $orderRepository,
        OrderManager $orderManager,
        PriceCalculationInterface $priceCalculation
    )
    {
        $this->em = $em;
        $this->session = $session;
        $this->orderRepository = $orderRepository;
        $this->orderManager = $orderManager; //FIXME: unused
        $this->priceCalculation = $priceCalculation;
    }

    /**
     * @param $user
     * @param null $locale
     *
     * @return null|\Sulu\Bundle\Sales\OrderBundle\Entity\Order
     */
    public function getUserCart($user = null, $locale = null)
    {
        // cart by session ID
        if (!$user) {
            // TODO: get correct locale
            // TODO: QUESTION: add locale to order?
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
            $cart = new Order();
        }

        $apiOrder = new ApiOrder($cart, $locale);

        // TODO: calcualte difference to previous cart
        
        $items = $apiOrder->getItems();

        // perform price calucaltion
        $totalPrice = $this->priceCalculation->calculate($items, $prices, $supplierItems, true);
        
        // set grouped items
        $apiOrder->setSupplierItems(array_values($supplierItems));
        // set total price
        $apiOrder->getTotalNetPrice($totalPrice);

        return $apiOrder;
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
                // TODO: QUESTION: delete expired carts?
                // delete expired carts
                if ($cart->getChanged()->getTimestamp() < strtotime(static::EXPIRY_MONTHS . ' months ago')) {
                    $this->em->remove($cart);
                    continue;
                }

                // dont delete first element, since this is the current cart
                if ($index === 0) {
                    continue;
                }
                // TODO: QUESTION remove duplicated carts? => if expiry check is active, only one cart can exist
                // remove duplicated carts
                $this->em->remove($cart);
            }
        }
    }

    public function addProduct($data, $user = null, $locale = null)
    {
        $cart = $this->getUserCart($user, $locale);
//        $cart->addItem();
    }
//    protected function checkProductData
}
